<?php
namespace App\Services;

use App\Models\TblFeriado;
use App\Models\TblSolicitudHe;
use App\Models\TblSeguimientoSolicitud;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class SolicitudHeService
{
     /**
     * Obtiene solicitudes filtradas por parámetros opcionales.
     *
     * @param array $filtros (ej: ['id_estado' => 1, 'cod_fiscalia' => 'X'])
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSolicitudesFiltradas(array $filtros = [], int $perPage = 10)
    {
        $query = TblSolicitudHe::query();
        foreach ($filtros as $campo => $valor) {
            if (!is_null($valor) && $valor !== '') {
                $query->where($campo, $valor);
            }
        }
        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Cambia el estado de una solicitud y registra el cambio en el log.
     *
     * @param int $solicitudId
     * @param int $nuevoEstadoId
     * @param string|null $comentario
     * @return bool
     */
    public function cambiarEstado(int $solicitudId, int $nuevoEstadoId, ?string $comentario = null): bool
    {
        return DB::transaction(function () use ($solicitudId, $nuevoEstadoId, $comentario) {
            $solicitud = TblSolicitudHe::findOrFail($solicitudId);
            $solicitud->id_estado = $nuevoEstadoId;
            $solicitud->save();

            TblSeguimientoSolicitud::create([
                'id_solicitud_he' => $solicitud->id,
                'username'        => Auth::user()->username,
                'id_estado'       => $nuevoEstadoId,
                'comentario'      => $comentario,
            ]);

            return true;
        });
    }

    /**
     * Cambia el estado de varias solicitudes y registra el seguimiento.
     *
     * @param array $idsSolicitudes
     * @param int $nuevoEstadoId
     * @param string|null $comentario
     * @return void
     */
    public function cambiarEstadoMultiple(array $idsSolicitudes, int $nuevoEstadoId, ?string $comentario = null): void
    {
        \Log::info('Entró a cambiarEstadoMultiple SolicitudHeService ');
        DB::transaction(function () use ($idsSolicitudes, $nuevoEstadoId, $comentario) {
           \Log::info('Entró a cambiarEstadoMultiple SolicitudHeService 2');
           \Log::info($idsSolicitudes);
            // 1. Update masivo de estado
            TblSolicitudHe::whereIn('id', $idsSolicitudes)
                ->update(['id_estado' => $nuevoEstadoId]);

            // 2. Insertar seguimiento para cada solicitud
            foreach ($idsSolicitudes as $id) {
                TblSeguimientoSolicitud::create([
                    'id_solicitud_he' => $id,
                    'username'        => Auth::user()->username,
                    'id_estado'       => $nuevoEstadoId,
                    // 'comentario'      => $comentario,
                ]);
            }
        });
    }

    // La función verEstados no debe estar aquí. Solo debe estar obtenerEstados, que retorna el array de estados.
    public function obtenerEstados($idSolicitud)
    {
        $seguimientos = \App\Models\TblSeguimientoSolicitud::where('id_solicitud_he', $idSolicitud)
            ->with('estado')
            ->orderBy('created_at')
            ->get();

        $estados = [];

        foreach ($seguimientos as $seguimiento) {
            $estados[] = [
                'idSolicitud' => $idSolicitud,
                'id' => $seguimiento->id,
                'gls_estado' => $seguimiento->estado->gls_estado ,
                'created_at' => $seguimiento->created_at->format('d/m/Y H:i'),
            ];
        }

        return $estados;
    }


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
