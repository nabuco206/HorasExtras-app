<?php

namespace App\Services;

use App\Models\TblFeriado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class SolicitudHeService
{
    /**
     * Calcula el porcentaje de horas extras según reglas:
     * - 25%: de 18:00 a 21:00 (días hábiles)
     * - 50%: después de las 21:00 (días hábiles), todo el día fines de semana y feriados
     */
    public function calculaPorcentaje($fecha, $horaInicio, $horaFin, $id_turno = 0)
    {
        try {
            // 1. VALIDAR ENTRADA
            $this->validarEntrada($fecha, $horaInicio, $horaFin, $id_turno);

            // 2. PARSEAR FECHAS Y HORAS
            $inicio = Carbon::parse("$fecha $horaInicio");
            $fin = Carbon::parse("$fecha $horaFin");
            if ($fin->lessThan($inicio)) {
                $fin->addDay(); // Cruce de medianoche
            }

            $min_reales = $inicio->diffInMinutes($fin);

            // 3. DETERMINAR CONTEXTO
            $mesdia = Carbon::parse($fecha)->format('m-d');
            $esFeriado = TblFeriado::where('fecha', $mesdia)->where('flag_activo', 1)->exists();
            $esFinDeSemana = in_array($inicio->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

            $min_25_tramo = 0;
            $min_50_tramo = 0;

            if ($esFeriado || $esFinDeSemana) {
                // Todo el tiempo es 50%
                $min_50_tramo = $min_reales;
            } else {
                // Día hábil: dividir en tramos
                $inicio_25 = Carbon::parse("$fecha 18:00");
                $fin_25 = Carbon::parse("$fecha 21:00");
                $inicio_50 = Carbon::parse("$fecha 21:00");

                // Tramo 25%: intersección entre [inicio, fin] y [18:00, 21:00]
                $inicio_tramo_25 = $inicio->copy()->max($inicio_25);
                $fin_tramo_25 = $fin->copy()->min($fin_25);
                if ($inicio_tramo_25->lessThan($fin_tramo_25)) {
                    $min_25_tramo = $inicio_tramo_25->diffInMinutes($fin_tramo_25);
                }

                // Tramo 50%: intersección entre [inicio, fin] y [21:00, fin]
                $inicio_tramo_50 = $inicio->copy()->max($inicio_50);
                if ($inicio_tramo_50->lessThan($fin)) {
                    $min_50_tramo = $inicio_tramo_50->diffInMinutes($fin);
                }
            }

            // Calcular recargos
            $recargo_25 = $min_25_tramo * 0.25;
            $recargo_50 = $min_50_tramo * 0.5;
            $total_min = $min_reales + $recargo_25 + $recargo_50;

            // Logging
            $this->logResultado($fecha, $horaInicio, $horaFin, [
                'min_reales' => $min_reales,
                'min_25' => $recargo_25,
                'min_50' => $recargo_50,
                'total_min' => $total_min,
                'contexto' => [
                    'es_feriado' => $esFeriado,
                    'es_fin_semana' => $esFinDeSemana,
                ]
            ]);

            return [
                'min_reales' => $min_reales,
                'min_25' => $recargo_25,
                'min_50' => $recargo_50,
                'total_min' => $total_min,
                'contexto' => [
                    'es_feriado' => $esFeriado,
                    'es_fin_semana' => $esFinDeSemana,
                ]
            ];
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
        $inicio = Carbon::createFromFormat('H:i', $horaInicio);
        $fin = Carbon::createFromFormat('H:i', $horaFin);

        // Si la hora de fin es menor que la de inicio, asumimos que cruza medianoche
        if ($fin->lessThan($inicio)) {
            return;
        }

        // Para horarios normales, la hora fin debe ser mayor que la de inicio
        if ($fin->lessThanOrEqualTo($inicio)) {
            throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
        }
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
}
