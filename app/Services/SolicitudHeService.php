<?php

namespace App\Services;

use App\Models\TblFeriado;
use App\Models\TblTurno;
use App\Models\TblConfigHorasExtras;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class SolicitudHeService
{
    /**
     * Calcula el porcentaje de horas extras basado en configuración de base de datos
     * 
     * @param string $fecha Fecha en formato Y-m-d
     * @param string $horaInicio Hora en formato H:i
     * @param string $horaFin Hora en formato H:i
     * @param int $id_turno ID del turno (opcional)
     * @return array Resultado con minutos calculados
     * @throws Exception Si hay errores de validación
     */
    public function calculaPorcentaje($fecha, $horaInicio, $horaFin, $id_turno = 0)
    {
        try {
            // 1. VALIDAR ENTRADA
            $this->validarEntrada($fecha, $horaInicio, $horaFin, $id_turno);
            
            // 2. PARSEAR FECHAS Y HORAS
            $fechaObj = Carbon::parse($fecha);
            $horaInicioObj = Carbon::createFromFormat('H:i', $horaInicio);
            $horaFinObj = Carbon::createFromFormat('H:i', $horaFin);
            
            // 3. CALCULAR DIFERENCIA EN MINUTOS (manejo de cruce de medianoche)
            if ($horaFinObj->lessThan($horaInicioObj)) {
                // Si cruza medianoche, agregar 24 horas a la hora final
                $horaFinObj->addDay();
            }
            $diferenciaMin = $horaInicioObj->diffInMinutes($horaFinObj);
            
            // 4. DETERMINAR CONTEXTO
            $contexto = $this->determinarContexto($fechaObj, $id_turno);
            
            // 5. CALCULAR MINUTOS POR PORCENTAJE
            $resultado = $this->calcularMinutosPorPorcentaje(
                $horaInicioObj, 
                $horaFinObj, 
                $contexto
            );
            
            // 6. AGREGAR INFORMACIÓN ADICIONAL
            $resultado['min_reales'] = $diferenciaMin;
            $resultado['fecha'] = $fecha;
            $resultado['contexto'] = $contexto;
            
            // 7. LOGGING
            $this->logResultado($fecha, $horaInicio, $horaFin, $resultado);
            
            return $resultado;
            
        } catch (Exception $e) {
            Log::error('Error en calculaPorcentaje', [
                'fecha' => $fecha,
                'horaInicio' => $horaInicio,
                'horaFin' => $horaFin,
                'id_turno' => $id_turno,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Validar datos de entrada
     */
    private function validarEntrada($fecha, $horaInicio, $horaFin, $id_turno)
    {
        $validator = Validator::make([
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'id_turno' => $id_turno
        ], [
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i',
            'id_turno' => 'integer|min:0'
        ]);
        
        if ($validator->fails()) {
            throw new Exception('Datos de entrada inválidos: ' . $validator->errors()->first());
        }
        
        // Validación personalizada para horarios que cruzan medianoche
        $inicio = \Carbon\Carbon::createFromFormat('H:i', $horaInicio);
        $fin = \Carbon\Carbon::createFromFormat('H:i', $horaFin);
        
        // Si la hora de fin es menor que la de inicio, asumimos que cruza medianoche
        if ($fin->lessThan($inicio)) {
            // Esto está permitido para turnos nocturnos
            return;
        }
        
        // Para horarios normales, la hora fin debe ser mayor que la de inicio
        if ($fin->lessThanOrEqualTo($inicio)) {
            throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
        }
    }
    
    /**
     * Determinar el contexto (feriado, fin de semana, día laboral)
     */
    private function determinarContexto(Carbon $fechaObj, int $id_turno): array
    {
        $mmdd = $fechaObj->format('m-d');
        $esFeriado = TblFeriado::where('fecha', $mmdd)->exists();
        $diaSemana = $fechaObj->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
        $esFinSemana = in_array($diaSemana, [6, 7]);
        
        $turno = null;
        if ($id_turno > 0) {
            $turno = TblTurno::find($id_turno);
        }
        
        return [
            'es_feriado' => $esFeriado,
            'es_fin_semana' => $esFinSemana,
            'dia_semana' => $diaSemana,
            'turno' => $turno,
            'fecha_obj' => $fechaObj
        ];
    }
    
    /**
     * Calcular minutos por porcentaje basado en configuración
     */
    private function calcularMinutosPorPorcentaje(Carbon $horaInicio, Carbon $horaFin, array $contexto): array
    {
        $configuraciones = TblConfigHorasExtras::activo()->ordenado()->get();
        
        $resultado = [
            'min_25' => 0,
            'min_50' => 0,
            'min_100' => 0,
            'total_min' => 0,
            'detalles' => []
        ];
        
        $inicio = $horaInicio->hour * 60 + $horaInicio->minute;
        $fin = $horaFin->hour * 60 + $horaFin->minute;
        
        // Manejar cruce de medianoche
        if ($fin < $inicio) {
            $fin += 24 * 60; // Agregar 1440 minutos (24 horas)
        }
        
        $diferenciaMin = $horaInicio->diffInMinutes($horaFin);
        
        foreach ($configuraciones as $config) {
            if (!$config->aplicaParaDia($contexto['dia_semana'], $contexto['es_feriado'])) {
                continue;
            }
            
            $minutosCalculados = $this->calcularMinutosParaConfiguracion(
                $inicio, 
                $fin, 
                $config, 
                $contexto
            );
            
            if ($minutosCalculados > 0) {
                $porcentaje = $config->porcentaje;
                // Calcular SOLO el recargo adicional (no incluir los minutos base)
                $minutosRecargo = $minutosCalculados * ($porcentaje / 100);
                
                // Categorizar por porcentaje
                switch ($porcentaje) {
                    case 25:
                        $resultado['min_25'] += $minutosRecargo;
                        break;
                    case 50:
                        $resultado['min_50'] += $minutosRecargo;
                        break;
                    case 100:
                        $resultado['min_100'] += $minutosRecargo;
                        break;
                }
                
                $resultado['detalles'][] = [
                    'configuracion' => $config->descripcion,
                    'minutos_reales' => $minutosCalculados,
                    'porcentaje' => $porcentaje,
                    'minutos_recargo' => $minutosRecargo
                ];
            }
        }
        
        // Total = minutos reales + todos los recargos
        $resultado['total_min'] = $diferenciaMin + $resultado['min_25'] + $resultado['min_50'] + $resultado['min_100'];
        
        return $resultado;
    }
    
    /**
     * Calcular minutos para una configuración específica
     */
    private function calcularMinutosParaConfiguracion(int $inicio, int $fin, TblConfigHorasExtras $config, array $contexto): int
    {
        // Si es feriado y la configuración aplica para feriados (todo el día)
        if ($contexto['es_feriado'] && $config->aplica_feriados) {
            if (!$config->hora_inicio && !$config->hora_fin) {
                return $fin - $inicio; // Todo el período
            }
        }
        
        // Si es un día específico (sábado o domingo) y no tiene horarios definidos (todo el día)
        if ($config->dias_semana && in_array($contexto['dia_semana'], $config->dias_semana)) {
            if (!$config->hora_inicio && !$config->hora_fin) {
                return $fin - $inicio; // Todo el período
            }
        }
        
        // Si tiene horarios específicos definidos
        if ($config->hora_inicio && $config->hora_fin) {
            $configInicio = Carbon::createFromFormat('H:i', $config->hora_inicio->format('H:i'));
            $configFin = Carbon::createFromFormat('H:i', $config->hora_fin->format('H:i'));
            
            $configInicioMin = $configInicio->hour * 60 + $configInicio->minute;
            $configFinMin = $configFin->hour * 60 + $configFin->minute;
            
            // Manejar cruce de medianoche en la configuración
            if ($configFinMin < $configInicioMin) {
                $configFinMin += 24 * 60;
            }
            
            // Ajustar el rango de trabajo si cruza medianoche
            $trabajoInicio = $inicio;
            $trabajoFin = $fin;
            
            // Cálculo de intersección
            $interseccionInicio = max($trabajoInicio, $configInicioMin);
            $interseccionFin = min($trabajoFin, $configFinMin);
            
            return max(0, $interseccionFin - $interseccionInicio);
        }
        
        return 0;
    }
    
    /**
     * Logging del resultado
     */
    private function logResultado($fecha, $horaInicio, $horaFin, array $resultado)
    {
        Log::info('Cálculo de horas extras realizado', [
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'min_reales' => $resultado['min_reales'],
            'min_25' => $resultado['min_25'],
            'min_50' => $resultado['min_50'],
            'total_min' => $resultado['total_min'],
            'contexto' => $resultado['contexto']['es_feriado'] ? 'feriado' : 
                          ($resultado['contexto']['es_fin_semana'] ? 'fin_semana' : 'laboral')
        ]);
    }
    
    /**
     * Obtener configuraciones activas para administración
     */
    public function obtenerConfiguraciones()
    {
        return TblConfigHorasExtras::activo()->ordenado()->get();
    }
    
    /**
     * Actualizar configuración
     */
    public function actualizarConfiguracion(array $datos)
    {
        $validator = Validator::make($datos, [
            'id' => 'required|exists:tbl_config_horas_extras,id',
            'porcentaje' => 'required|numeric|min:0|max:200',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'activo' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            throw new Exception('Datos inválidos para actualización: ' . $validator->errors()->first());
        }
        
        $config = TblConfigHorasExtras::findOrFail($datos['id']);
        $config->update($datos);
        
        Log::info('Configuración de horas extras actualizada', [
            'id' => $config->id,
            'clave' => $config->clave,
            'cambios' => $datos
        ]);
        
        return $config;
    }
}
