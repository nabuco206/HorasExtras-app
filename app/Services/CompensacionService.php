<?php

namespace App\Services;

use App\Models\TblSolicitudCompensa;
use App\Models\TblBolsonTiempo;
use App\Models\TblBolsonHist;
use App\Services\FlujoEstadoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompensacionService
{
    protected $bolsonService;
    protected $flujoEstadoService;

    public function __construct(BolsonService $bolsonService, FlujoEstadoService $flujoEstadoService)
    {
        $this->bolsonService = $bolsonService;
        $this->flujoEstadoService = $flujoEstadoService;
    }

    /**
     * Procesar aprobación de una solicitud de compensación
     * En el nuevo flujo: descuento ya aplicado, solo cambiar estado a aprobado
     */
    public function procesarAprobacionCompensacion(TblSolicitudCompensa $solicitud, $minutosAprobados = null, $aprobadoPor = null): array
    {
        try {
            DB::beginTransaction();

            // Usar minutos solicitados si no se especifican aprobados
            $minutosADescontar = $minutosAprobados ?? $solicitud->minutos_solicitados;

            // Verificar que la compensación esté en estado SOLICITADA (descuento ya aplicado)
            if ($solicitud->id_estado != 8) { // 8 = COMPENSACION_SOLICITADA
                DB::rollBack();
                return [
                    'exitoso' => false,
                    'mensaje' => "La compensación debe estar en estado SOLICITADA para poder aprobarla"
                ];
            }

            // Actualizar la solicitud con los datos de aprobación
            $solicitud->update([
                'minutos_aprobados' => $minutosADescontar,
                'aprobado_por' => $aprobadoPor,
                'fecha_aprobacion' => now()
            ]);

            // Usar el workflow para cambiar al estado de aprobación final (sin descuento adicional)
            $estadoAprobado = \App\Models\TblEstado::where('codigo', 'COMPENSACION_APROBADA_JEFE')->first();

            if (!$estadoAprobado) {
                DB::rollBack();
                return [
                    'exitoso' => false,
                    'mensaje' => 'Estado COMPENSACION_APROBADA_JEFE no encontrado'
                ];
            }

            // Ejecutar transición de aprobación (no hace descuento, ya se hizo al solicitar)
            $resultadoTransicion = $this->flujoEstadoService->ejecutarTransicionModelo(
                $solicitud->id,
                $estadoAprobado->id,
                $aprobadoPor ?? 'SISTEMA',
                "Compensación aprobada por jefe - {$minutosADescontar} min",
                'TblSolicitudCompensa'
            );

            if (!$resultadoTransicion['exitoso']) {
                DB::rollBack();
                return [
                    'exitoso' => false,
                    'mensaje' => 'Error en workflow: ' . $resultadoTransicion['mensaje']
                ];
            }

            DB::commit();

            Log::info("Compensación aprobada vía workflow", [
                'solicitud_id' => $solicitud->id,
                'username' => $solicitud->username,
                'minutos_aprobados' => $minutosADescontar,
                'aprobado_por' => $aprobadoPor,
                'estado_destino' => $estadoAprobado->codigo
            ]);

            return [
                'exitoso' => true,
                'mensaje' => "✅ Compensación aprobada por jefe. Ciclo completado exitosamente.",
                'solicitud' => $solicitud->fresh(),
                'transicion' => $resultadoTransicion
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar aprobación de compensación", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);

            return [
                'exitoso' => false,
                'mensaje' => 'Error al procesar compensación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar rechazo de una solicitud de compensación
     * En el nuevo flujo: devolver minutos al bolsón
     */
    public function procesarRechazoCompensacion(TblSolicitudCompensa $solicitud, $aprobadoPor = null, $observaciones = null): array
    {
        try {
            DB::beginTransaction();

            // Actualizar datos del rechazo
            $solicitud->update([
                'aprobado_por' => $aprobadoPor,
                'fecha_aprobacion' => now(),
                'observaciones' => $observaciones ? $solicitud->observaciones . ' | RECHAZO: ' . $observaciones : $solicitud->observaciones
            ]);

            // Usar el workflow para cambiar al estado de rechazo (devuelve minutos)
            $estadoRechazado = \App\Models\TblEstado::where('codigo', 'COMPENSACION_RECHAZADA_JEFE')->first();

            if (!$estadoRechazado) {
                DB::rollBack();
                return [
                    'exitoso' => false,
                    'mensaje' => 'Estado COMPENSACION_RECHAZADA_JEFE no encontrado'
                ];
            }

            // Ejecutar transición que devolverá los minutos al bolsón
            $resultadoTransicion = $this->flujoEstadoService->ejecutarTransicionModelo(
                $solicitud->id,
                $estadoRechazado->id,
                $aprobadoPor ?? 'SISTEMA',
                "Compensación rechazada - Devolviendo {$solicitud->minutos_solicitados} min al bolsón",
                'TblSolicitudCompensa'
            );

            if (!$resultadoTransicion['exitoso']) {
                DB::rollBack();
                return [
                    'exitoso' => false,
                    'mensaje' => 'Error en workflow de rechazo: ' . $resultadoTransicion['mensaje']
                ];
            }

            DB::commit();

            Log::info("Compensación rechazada con devolución de minutos", [
                'solicitud_id' => $solicitud->id,
                'username' => $solicitud->username,
                'minutos_devueltos' => $solicitud->minutos_solicitados,
                'rechazado_por' => $aprobadoPor
            ]);

            return [
                'exitoso' => true,
                'mensaje' => "✅ Compensación rechazada. Minutos devueltos al bolsón automáticamente.",
                'solicitud' => $solicitud->fresh(),
                'transicion' => $resultadoTransicion
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar rechazo de compensación", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);

            return [
                'exitoso' => false,
                'mensaje' => 'Error al procesar rechazo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar si una solicitud puede ser aprobada
     */
    public function validarSolicitudParaAprobacion(TblSolicitudCompensa $solicitud): array
    {
        // Verificar que esté en estado solicitada (pendiente de aprobación)
        if ($solicitud->id_estado != 8) { // 8 = COMPENSACION_SOLICITADA
            return [
                'valida' => false,
                'mensaje' => 'La solicitud no está pendiente de aprobación'
            ];
        }

        // Verificar que la fecha de compensación no haya pasado
        if ($solicitud->fecha_solicitud < now()->toDateString()) {
            return [
                'valida' => false,
                'mensaje' => 'La fecha de compensación ya pasó'
            ];
        }

        // No verificamos saldo disponible porque ya se descontó al crear la solicitud

        return [
            'valida' => true,
            'mensaje' => 'Solicitud válida para aprobación'
        ];
    }

    /**
     * Obtener solicitudes pendientes de aprobación
     */
    public function obtenerSolicitudesPendientes($codFiscalia = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = TblSolicitudCompensa::with(['persona', 'estado'])
            ->where('id_estado', 1) // PENDIENTE
            ->orderBy('fecha_solicitud', 'asc');

        if ($codFiscalia) {
            $query->where('cod_fiscalia', $codFiscalia);
        }

        return $query->get();
    }

    /**
     * Procesar múltiples aprobaciones de compensaciones
     */
    public function procesarAprobacionesMultiples(array $solicitudesIds, $aprobadoPor = null): array
    {
        try {
            DB::beginTransaction();

            $resultado = [
                'exitoso' => true,
                'procesadas' => 0,
                'errores' => [],
                'compensaciones_aprobadas' => [],
                'total_minutos_descontados' => 0,
                'mensaje' => ''
            ];

            $solicitudes = TblSolicitudCompensa::whereIn('id', $solicitudesIds)->get();

            foreach ($solicitudes as $solicitud) {
                try {
                    $resultadoIndividual = $this->procesarAprobacionCompensacion($solicitud, null, $aprobadoPor);

                    if ($resultadoIndividual['exitoso']) {
                        $resultado['procesadas']++;
                        $resultado['compensaciones_aprobadas'][] = [
                            'id' => $solicitud->id,
                            'username' => $solicitud->username,
                            'minutos_aprobados' => $solicitud->minutos_aprobados,
                            'fecha_solicitud' => $solicitud->fecha_solicitud->format('Y-m-d')
                        ];
                        $resultado['total_minutos_descontados'] += $solicitud->minutos_aprobados;
                    } else {
                        $resultado['errores'][] = [
                            'solicitud_id' => $solicitud->id,
                            'error' => $resultadoIndividual['mensaje']
                        ];
                    }

                } catch (\Exception $e) {
                    $resultado['errores'][] = [
                        'solicitud_id' => $solicitud->id,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $resultado['exitoso'] = $resultado['procesadas'] > 0;

            if ($resultado['exitoso']) {
                $resultado['mensaje'] = "✅ Procesadas {$resultado['procesadas']} compensaciones. " .
                                      "Total descontado: {$resultado['total_minutos_descontados']} min.";
                DB::commit();
            } else {
                DB::rollBack();
                $resultado['mensaje'] = "❌ No se pudo procesar ninguna compensación.";
            }

            return $resultado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en aprobación masiva de compensaciones", [
                'solicitudes_ids' => $solicitudesIds,
                'error' => $e->getMessage()
            ]);

            return [
                'exitoso' => false,
                'mensaje' => 'Error en aprobación masiva: ' . $e->getMessage(),
                'procesadas' => 0,
                'errores' => [['error' => $e->getMessage()]]
            ];
        }
    }

    /**
     * Obtener estadísticas de compensaciones
     */
    public function obtenerEstadisticas($username = null): array
    {
        $query = TblSolicitudCompensa::query();

        if ($username) {
            $query->where('username', $username);
        }

        $pendientes = (clone $query)->where('id_estado', 8)->count(); // COMPENSACION_SOLICITADA
        $aprobadas = (clone $query)->where('id_estado', 9)->count(); // COMPENSACION_APROBADA_JEFE
        $rechazadas = (clone $query)->where('id_estado', 10)->count(); // COMPENSACION_RECHAZADA_JEFE

        $minutosAprobados = (clone $query)->where('id_estado', 9)->sum('minutos_aprobados');
        $minutosSolicitados = (clone $query)->where('id_estado', 8)->sum('minutos_solicitados');

        return [
            'total_solicitudes' => $pendientes + $aprobadas + $rechazadas,
            'pendientes' => $pendientes,
            'aprobadas' => $aprobadas,
            'rechazadas' => $rechazadas,
            'minutos_aprobados_total' => $minutosAprobados ?? 0,
            'minutos_pendientes_total' => $minutosSolicitados ?? 0,
            'porcentaje_aprobacion' => ($aprobadas + $rechazadas) > 0 ? round(($aprobadas / ($aprobadas + $rechazadas)) * 100, 1) : 0
        ];
    }
}
