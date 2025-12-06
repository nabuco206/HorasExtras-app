<?php

namespace App\Services;

use App\Models\TblBolsonTiempo;
use App\Models\TblBolsonHist;
use App\Models\TblSolicitudCompensa;
use App\Models\TblSolicitudHe;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BolsonService
{
    /**
     * Crear bolsón pendiente cuando se ingresa una solicitud HE
     */
    public function crearBolsonPendiente(TblSolicitudHe $solicitud): ?TblBolsonTiempo
    {
        try {
            DB::beginTransaction();

            // Calcular minutos de la solicitud
            $minutosCalculados = $this->calcularMinutosSolicitud($solicitud);

            if ($minutosCalculados <= 0) {
                // Si no se pueden calcular por horas, usar total_min de la solicitud
                $minutosCalculados = $solicitud->total_min ?? 0;
            }

            if ($minutosCalculados <= 0) {
                DB::rollBack();
                Log::warning("No se pudieron determinar minutos para bolsón pendiente", [
                    'solicitud_id' => $solicitud->id
                ]);
                return null;
            }

            // Crear entrada en el bolsón con estado PENDIENTE
            $bolson = TblBolsonTiempo::create([
                'username' => $solicitud->username,
                'id_solicitud_he' => $solicitud->id,
                'minutos' => $minutosCalculados,
                'saldo_min' => $minutosCalculados,
                'fecha_crea' => now()->toDateString(),
                'fecha_vence' => now()->addYear()->toDateString(),
                'origen' => 'HE_PENDIENTE',
                'estado' => 'PENDIENTE',
                'activo' => true
            ]);

            // Registrar en historial
            $this->registrarHistorial(
                $bolson,
                'SOLICITUD_USUARIO',
                $minutosCalculados,
                0,
                $minutosCalculados,
                "Bolsón pendiente creado por solicitud HE #{$solicitud->id} - En espera de aprobación",
                null
            );

            DB::commit();
            Log::info("Bolsón pendiente creado exitosamente", [
                'bolson_id' => $bolson->id,
                'solicitud_id' => $solicitud->id,
                'minutos' => $minutosCalculados,
                'estado' => 'PENDIENTE'
            ]);

            return $bolson;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear bolsón pendiente", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Procesar solicitud de HE aprobada y activar bolsón
     */
    public function procesarSolicitudHeAprobada(TblSolicitudHe $solicitud): ?TblBolsonTiempo
    {
        try {
            DB::beginTransaction();

            // Buscar si ya existe un bolsón pendiente para esta solicitud
            $bolsonExistente = TblBolsonTiempo::where('id_solicitud_he', $solicitud->id)
                ->where('estado', 'PENDIENTE')
                ->first();

            if ($bolsonExistente) {
                // Activar el bolsón existente
                $bolsonExistente->update([
                    'estado' => 'DISPONIBLE',
                    'origen' => 'HE_APROBADA'
                ]);

                // Registrar en historial
                $this->registrarHistorial(
                    $bolsonExistente,
                    'APROBACION',
                    0,
                    $bolsonExistente->saldo_min,
                    $bolsonExistente->saldo_min,
                    "Bolsón aprobado - Tiempo disponible para compensación",
                    null
                );

                DB::commit();
                Log::info("Bolsón activado por aprobación", [
                    'bolson_id' => $bolsonExistente->id,
                    'solicitud_id' => $solicitud->id,
                    'minutos' => $bolsonExistente->minutos
                ]);

                return $bolsonExistente;
            }

            // Si no existe bolsón pendiente, crear uno nuevo directamente disponible
            $minutosAprobados = $this->calcularMinutosSolicitud($solicitud);
            if ($minutosAprobados <= 0) {
                $minutosAprobados = $solicitud->total_min ?? 0;
            }

            if ($minutosAprobados <= 0) {
                DB::rollBack();
                return null;
            }

            $bolson = TblBolsonTiempo::create([
                'username' => $solicitud->username,
                'id_solicitud_he' => $solicitud->id,
                'minutos' => $minutosAprobados,
                'saldo_min' => $minutosAprobados,
                'fecha_crea' => now()->toDateString(),
                'fecha_vence' => now()->addYear()->toDateString(),
                'origen' => 'HE_APROBADA',
                'estado' => 'DISPONIBLE',
                'activo' => true
            ]);

            // Registrar en historial
            $this->registrarHistorial(
                $bolson,
                'CREACION',
                $minutosAprobados,
                0,
                $minutosAprobados,
                "Bolsón creado y aprobado por solicitud HE #{$solicitud->id}",
                null
            );

            DB::commit();
            Log::info("Bolsón creado directamente como disponible", [
                'bolson_id' => $bolson->id,
                'minutos' => $minutosAprobados
            ]);

            return $bolson;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar solicitud HE aprobada", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Descontar minutos del bolsón usando lógica FIFO
     */
    public function descontarMinutos(string $username, int $minutosADescontar, string $concepto = 'Descuento', ?int $idSolicitudCompensa = null): array
    {
        try {
            DB::beginTransaction();

            $resultado = [
                'success' => false,
                'minutos_descontados' => 0,
                'minutos_faltantes' => $minutosADescontar,
                'bolsones_afectados' => [],
                'mensaje' => ''
            ];

            // Obtener bolsones vigentes ordenados por FIFO (más antiguos primero)
            $bolsonesVigentes = TblBolsonTiempo::vigentes()
                ->where('username', $username)
                ->where('saldo_min', '>', 0)
                ->orderBy('fecha_creacion', 'asc')
                ->get();

            if ($bolsonesVigentes->isEmpty()) {
                $resultado['mensaje'] = 'No hay bolsones vigentes disponibles';
                DB::rollBack();
                return $resultado;
            }

            $totalDisponible = $bolsonesVigentes->sum('saldo_min');
            if ($totalDisponible < $minutosADescontar) {
                $resultado['mensaje'] = "Saldo insuficiente. Disponible: {$totalDisponible} min, Requerido: {$minutosADescontar} min";
                DB::rollBack();
                return $resultado;
            }

            $minutosRestantes = $minutosADescontar;

            foreach ($bolsonesVigentes as $bolson) {
                if ($minutosRestantes <= 0) break;

                $descontarDeBolson = min($minutosRestantes, $bolson->saldo_min);
                $saldoAnterior = $bolson->saldo_min;
                $nuevoSaldo = $saldoAnterior - $descontarDeBolson;

                // Actualizar saldo del bolsón
                $bolson->update(['saldo_min' => $nuevoSaldo]);

                // Si se agotó el bolsón, marcarlo como inactivo
                if ($nuevoSaldo <= 0) {
                    $bolson->update(['activo' => false]);
                }

                // Registrar en historial
                $this->registrarHistorial(
                    $bolson,
                    'DESCUENTO_POR_SOLICITUD',
                    $descontarDeBolson,
                    $saldoAnterior,
                    $nuevoSaldo,
                    $concepto,
                    $idSolicitudCompensa
                );

                $resultado['bolsones_afectados'][] = [
                    'bolson_id' => $bolson->id,
                    'minutos_descontados' => $descontarDeBolson,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo' => $nuevoSaldo
                ];

                $minutosRestantes -= $descontarDeBolson;
                $resultado['minutos_descontados'] += $descontarDeBolson;
            }

            $resultado['minutos_faltantes'] = $minutosRestantes;
            $resultado['success'] = $minutosRestantes <= 0;
            $resultado['mensaje'] = $resultado['success']
                ? "Descuento aplicado exitosamente"
                : "Descuento parcial aplicado";

            DB::commit();
            Log::info("Descuento de bolsón aplicado", $resultado);

            return $resultado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al descontar minutos del bolsón", [
                'username' => $username,
                'minutos' => $minutosADescontar,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Expirar bolsones vencidos
     */
    public function expirarBolsonesVencidos()
    {
        $vencidos = TblBolsonTiempo::where('activo', true)
            ->where('saldo_min', '>', 0)
            ->where('fecha_vence', '<=', now())
            ->get();

        $expirados = [];

        foreach ($vencidos as $bolson) {
            $saldoAnterior = $bolson->saldo_min;

            $bolson->update([
                'activo' => false,
                'saldo_min' => 0
            ]);

            $this->registrarHistorial(
                $bolson,
                'EXPIRACION',
                $saldoAnterior,
                $saldoAnterior,
                0,
                'Bolsón expirado por vencimiento',
                null
            );

            $expirados[] = [
                'bolson_id' => $bolson->id,
                'username' => $bolson->username,
                'minutos_perdidos' => $saldoAnterior,
                'fecha_vence' => $bolson->fecha_vence->toDateString()
            ];
        }

        if (!empty($expirados)) {
            Log::info("Bolsones expirados", ['count' => count($expirados), 'detalles' => $expirados]);
        }

        return $expirados;
    }

    /**
     * Obtener resumen de bolsones para un usuario
     */
    public function obtenerResumenBolsones(string $username): array
    {
        $vigentes = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->orderBy('fecha_crea', 'asc')
            ->get();

        $totalMinutos = $vigentes->sum('saldo_min');
        $proximoVencimiento = $vigentes->where('saldo_min', '>', 0)->min('fecha_vence');

        return [
            'total_bolsones' => $vigentes->count(),
            'total_minutos' => $totalMinutos,
            'total_minutos_formato' => $totalMinutos . ' min',
            'proximo_vencimiento' => $proximoVencimiento ? $proximoVencimiento->format('Y-m-d') : null,
            'bolsones_detalle' => $vigentes->map(function ($bolson) {
                return [
                    'id' => $bolson->id,
                    'minutos_iniciales' => $bolson->minutos,
                    'saldo_actual' => $bolson->saldo_min,
                    'fecha_creacion' => $bolson->fecha_crea->format('Y-m-d'),
                    'fecha_vence' => $bolson->fecha_vence->format('Y-m-d'),
                    'descripcion' => $bolson->origen ?? 'HE Aprobada'
                ];
            })->toArray()
        ];
    }

    /**
     * Simular descuento para testing
     */
    public function simularDescuento(string $username, int $minutosADescontar): array
    {
        $bolsonesVigentes = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->where('saldo_min', '>', 0)
            ->orderBy('fecha_crea', 'asc')
            ->get();

        $simulacion = [
            'factible' => false,
            'total_disponible' => $bolsonesVigentes->sum('saldo_min'),
            'minutos_solicitados' => $minutosADescontar,
            'distribucion' => []
        ];

        if ($simulacion['total_disponible'] < $minutosADescontar) {
            $simulacion['mensaje'] = 'Saldo insuficiente';
            return $simulacion;
        }

        $minutosRestantes = $minutosADescontar;
        foreach ($bolsonesVigentes as $bolson) {
            if ($minutosRestantes <= 0) break;

            $descontarDeBolson = min($minutosRestantes, $bolson->saldo_min);

            $simulacion['distribucion'][] = [
                'bolson_id' => $bolson->id,
                'fecha_creacion' => $bolson->fecha_crea->toDateString(),
                'saldo_actual' => $bolson->saldo_min,
                'minutos_a_descontar' => $descontarDeBolson,
                'saldo_resultante' => $bolson->saldo_min - $descontarDeBolson
            ];

            $minutosRestantes -= $descontarDeBolson;
        }

        $simulacion['factible'] = $minutosRestantes <= 0;
        $simulacion['mensaje'] = $simulacion['factible'] ? 'Simulación exitosa' : 'Saldo insuficiente';

        return $simulacion;
    }

    /**
     * Calcular minutos de una solicitud HE
     * Usa el total_min que ya incluye los cálculos de porcentajes (25% y 50%)
     */
    private function calcularMinutosSolicitud(TblSolicitudHe $solicitud): int
    {
        // Primero intentar usar el total_min que ya está calculado con porcentajes
        if ($solicitud->total_min && $solicitud->total_min > 0) {
            return $solicitud->total_min;
        }

        // Fallback: calcular minutos reales si no hay total_min
        if (!$solicitud->fecha || !$solicitud->hrs_inicial || !$solicitud->hrs_final) {
            return 0;
        }

        $fechaSolo = $solicitud->fecha->format('Y-m-d');
        $inicio = Carbon::parse($fechaSolo . ' ' . $solicitud->hrs_inicial);
        $fin = Carbon::parse($fechaSolo . ' ' . $solicitud->hrs_final);

        // Si hora fin es menor que hora inicio, asumimos que cruza medianoche
        if ($fin->lt($inicio)) {
            $fin->addDay();
        }

        return $inicio->diffInMinutes($fin);
    }

    /**
     * Registrar movimiento en historial
     */
    private function registrarHistorial(
        TblBolsonTiempo $bolson,
        string $tipoMovimiento,
        int $minutos,
        int $saldoAnterior,
        int $saldoNuevo,
        string $observaciones = null,
        ?int $idSolicitudCompensa = null
    ): TblBolsonHist {
        // Si estamos registrando una creación de devolución y no se pasó el id de compensación,
        // loggear el backtrace para identificar la llamada que no propagó el id.
        if ($tipoMovimiento === 'CREACION_DEVOLUCION' && empty($idSolicitudCompensa)) {
            try {
                Log::warning('registrarHistorial: CREACION_DEVOLUCION sin id_solicitud_compensa', [
                    'bolson_id' => $bolson->id ?? null,
                    'username' => $bolson->username ?? null,
                    'minutos' => $minutos,
                    'observaciones' => $observaciones,
                    'backtrace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10))->map(function($b) {
                        return isset($b['file']) ? ($b['file'] . ':' . ($b['line'] ?? '')) : (isset($b['class']) ? $b['class'] : json_encode($b));
                    })->toArray()
                ]);
            } catch (\Exception $e) {
                Log::warning('registrarHistorial: fallo al loggear backtrace: ' . $e->getMessage());
            }
        }

        return TblBolsonHist::create([
            'id_bolson_tiempo' => $bolson->id,
            'username' => $bolson->username,
            'accion' => $tipoMovimiento,
            'minutos_afectados' => $minutos,
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => $saldoNuevo,
            'observaciones' => $observaciones,
            'id_solicitud_compensa' => $idSolicitudCompensa
        ]);
    }

    /**
     * Obtener historial de un bolsón específico
     */
    public function obtenerHistorialBolson(int $bolsonId): array
    {
        $historial = TblBolsonHist::where('bolson_id', $bolsonId)
            ->orderBy('fecha_movimiento', 'desc')
            ->get();

        return $historial->map(function ($registro) {
            return [
                'id' => $registro->id,
                'tipo_movimiento' => $registro->tipo_movimiento,
                'minutos' => $registro->minutos,
                'saldo_anterior' => $registro->saldo_anterior,
                'saldo_nuevo' => $registro->saldo_nuevo,
                'fecha_movimiento' => $registro->fecha_movimiento->format('Y-m-d H:i:s'),
                'observaciones' => $registro->observaciones
            ];
        })->toArray();
    }

    /**
     * Verificar si un usuario tiene saldo suficiente
     */
    public function tieneSaldoSuficiente(string $username, int $minutosRequeridos): bool
    {
        $totalDisponible = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->sum('saldo_min');

        return $totalDisponible >= $minutosRequeridos;
    }

    /**
     * Obtener saldo disponible de un usuario
     */
    public function obtenerSaldoDisponible(string $username): int
    {
        return TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->sum('saldo_min');
    }

    /**
     * Obtener detalle del saldo de un usuario
     */
    public function obtenerDetalleSaldo(string $username): array
    {
        $bolsones = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->orderBy('fecha_crea', 'asc')
            ->get();

        return $bolsones->map(function ($bolson) {
            $diasRestantes = now()->diffInDays($bolson->fecha_vence, false);

            return [
                'id' => $bolson->id,
                'solicitud_he_id' => $bolson->id_solicitud_he,
                'minutos_iniciales' => $bolson->minutos,
                'minutos_disponibles' => $bolson->saldo_min,
                'saldo_min' => $bolson->saldo_min,
                'fecha_vence' => $bolson->fecha_vence->toDateString(),
                'fecha_vencimiento' => $bolson->fecha_vence->toDateString(),
                'dias_restantes' => $diasRestantes,
                'descripcion' => $bolson->descripcion ?? 'Bolsón de HE'
            ];
        })->toArray();
    }

    /**
     * Obtener resumen detallado de un usuario
     */
    public function obtenerResumenDetallado(string $username): array
    {
        $bolsones = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->orderBy('fecha_crea', 'asc')
            ->get();

        $totalMinutos = $bolsones->sum('saldo_min');

        // Obtener bolsones que vencen en los próximos 7 días
        $bolsonesProximosVencer = [];
        foreach ($bolsones as $bolson) {
            if ($bolson->fecha_vence <= now()->addWeek()) {
                $diasParaVencer = now()->diffInDays($bolson->fecha_vence, false);
                $bolsonesProximosVencer[] = [
                    'id' => $bolson->id,
                    'saldo_min' => $bolson->saldo_min,
                    'fecha_vence' => $bolson->fecha_vence->toDateString(),
                    'dias_para_vencer' => $diasParaVencer,
                    'urgente' => $diasParaVencer <= 2
                ];
            }
        }

        return [
            'total_minutos' => $totalMinutos,
            'total_bolsones' => $bolsones->count(),
            'bolsones_proximos_vencer' => $bolsonesProximosVencer,
            'bolsones' => $bolsones->map(function ($bolson) {
                return [
                    'id' => $bolson->id,
                    'saldo_min' => $bolson->saldo_min,
                    'fecha_crea' => $bolson->fecha_crea->toDateString(),
                    'fecha_vence' => $bolson->fecha_vence->toDateString(),
                    'descripcion' => $bolson->descripcion ?? 'Sin descripción'
                ];
            })->toArray()
        ];
    }

    /**
     * Obtener bolsones pendientes de un usuario
     */
    public function obtenerBolsonesPendientes(string $username): array
    {
        $bolsones = TblBolsonTiempo::pendientes()
            ->where('username', $username)
            ->with('solicitudHe.estado')
            ->orderBy('fecha_crea', 'desc')
            ->get();

        return $bolsones->map(function ($bolson) {
            return [
                'id' => $bolson->id,
                'solicitud_he_id' => $bolson->id_solicitud_he,
                'minutos' => $bolson->minutos,
                'fecha_crea' => $bolson->fecha_crea,
                'estado' => $bolson->estado,
                'solicitud_estado' => $bolson->solicitudHe?->estado?->gls_estado ?? 'Sin estado',
                'observaciones' => "Pendiente de aprobación - Solicitud HE #{$bolson->id_solicitud_he}"
            ];
        })->toArray();
    }

    /**
     * Obtener resumen completo incluyendo pendientes y disponibles
     */
    public function obtenerResumenCompleto(string $username): array
    {
        $disponibles = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->orderBy('fecha_crea', 'asc')
            ->get();

        $pendientes = TblBolsonTiempo::pendientes()
            ->where('username', $username)
            ->orderBy('fecha_crea', 'desc')
            ->get();

        $totalDisponible = $disponibles->sum('saldo_min');
        $totalPendiente = $pendientes->sum('saldo_min');

        return [
            'total_disponible' => $totalDisponible,
            'total_pendiente' => $totalPendiente,
            'total_general' => $totalDisponible + $totalPendiente,
            'bolsones_disponibles' => $disponibles->count(),
            'bolsones_pendientes' => $pendientes->count(),
            'detalle_disponibles' => $disponibles->map(function ($bolson) {
                return [
                    'id' => $bolson->id,
                    'minutos_disponibles' => $bolson->saldo_min,
                    'fecha_vence' => $bolson->fecha_vence->toDateString(),
                    'estado' => $bolson->estado,
                    'origen' => $bolson->origen
                ];
            })->toArray(),
            'detalle_pendientes' => $pendientes->map(function ($bolson) {
                return [
                    'id' => $bolson->id,
                    'minutos_pendientes' => $bolson->saldo_min,
                    'fecha_crea' => $bolson->fecha_crea,
                    'estado' => $bolson->estado,
                    'solicitud_he_id' => $bolson->id_solicitud_he
                ];
            })->toArray()
        ];
    }

    /**
     * Obtener estadísticas generales del sistema de bolsones
     */
    public function obtenerEstadisticasGenerales(): array
    {
        return [
            'total_bolsones_activos' => TblBolsonTiempo::where('activo', true)->count(),
            'total_minutos_disponibles' => TblBolsonTiempo::vigentes()->sum('saldo_min'),
            'total_minutos_pendientes' => TblBolsonTiempo::pendientes()->sum('saldo_min'),
            'usuarios_con_bolsones' => TblBolsonTiempo::vigentes()
                ->distinct('username')
                ->count('username'),
            'usuarios_con_pendientes' => TblBolsonTiempo::pendientes()
                ->distinct('username')
                ->count('username'),
            'bolsones_por_vencer_7_dias' => TblBolsonTiempo::vigentes()
                ->where('fecha_vence', '<=', now()->addWeek())
                ->count(),
        ];
    }

    /**
     * Crear bolsón de devolución cuando se rechaza una compensación
     */
    public function crearBolsonDevolución($username, $minutos, $concepto = 'Devolución por compensación rechazada', ?int $idSolicitudCompensa = null): array
    {
        try {
            DB::beginTransaction();

            // Crear nuevo bolsón con los minutos devueltos usando campos correctos
            $bolsonDevolucion = new TblBolsonTiempo();
            $bolsonDevolucion->username = $username;
            // Para devoluciones no asociadas a una solicitud HE concreta, dejar NULL
            // (la columna se hará nullable mediante migración). Evita usar IDs "mágicos".
            $bolsonDevolucion->id_solicitud_he = null;
            $bolsonDevolucion->fecha_crea = now()->format('Y-m-d');
            $bolsonDevolucion->fecha_vence = now()->addYear()->format('Y-m-d'); // 1 año de vigencia
            $bolsonDevolucion->minutos = $minutos;
            $bolsonDevolucion->saldo_min = $minutos;
            $bolsonDevolucion->origen = 'DEVOLUCION_COMPENSACION';
            $bolsonDevolucion->estado = 'DISPONIBLE';
            $bolsonDevolucion->activo = true;
            $bolsonDevolucion->save();

            // Registrar en el historial (incluir id de compensación si está disponible)
            $this->registrarHistorial(
                $bolsonDevolucion,
                'CREACION_DEVOLUCION',
                $minutos,
                0,
                $minutos,
                $concepto . ' - Bolsón ID: ' . $bolsonDevolucion->id,
                $idSolicitudCompensa
            );

            DB::commit();

            Log::info("Bolsón de devolución creado exitosamente", [
                'bolson_id' => $bolsonDevolucion->id,
                'username' => $username,
                'minutos_devueltos' => $minutos,
                'concepto' => $concepto
            ]);

            return [
                'success' => true,
                'bolson' => $bolsonDevolucion,
                'mensaje' => "Bolsón de devolución creado: {$minutos} min disponibles"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            // Registrar error completo y propagar resultado claro al llamador
            Log::error("Error al crear bolsón de devolución", [
                'username' => $username,
                'minutos' => $minutos,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'mensaje' => 'Error al crear bolsón de devolución: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}
