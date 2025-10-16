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
     * Procesar solicitud de HE aprobada y crear entrada en bolsón
     */
    public function procesarSolicitudHeAprobada(TblSolicitudHe $solicitud): ?TblBolsonTiempo
    {
        try {
            DB::beginTransaction();

            // Calcular minutos de la solicitud aprobada
            $minutosAprobados = $this->calcularMinutosSolicitud($solicitud);

            if ($minutosAprobados <= 0) {
                DB::rollBack();
                return null;
            }

            // Crear entrada en el bolsón
            $bolson = TblBolsonTiempo::create([
                'username' => $solicitud->username,
                'id_solicitud_he' => $solicitud->id,
                'minutos' => $minutosAprobados,
                'saldo_min' => $minutosAprobados,
                'fecha_crea' => now()->toDateString(),
                'fecha_vence' => now()->addYear()->toDateString(),
                'origen' => 'HE_APROBADA',
                'activo' => true
            ]);

            // Registrar en historial
            $this->registrarHistorial(
                $bolson,
                'CREACION',
                $minutosAprobados,
                0,
                $minutosAprobados,
                "Bolsón creado por solicitud HE #{$solicitud->id}"
            );

            DB::commit();
            Log::info("Bolsón creado exitosamente", ['bolson_id' => $bolson->id, 'minutos' => $minutosAprobados]);

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
    public function descontarMinutos(string $username, int $minutosADescontar, string $concepto = 'Descuento'): array
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
                    'DESCUENTO',
                    $descontarDeBolson,
                    $saldoAnterior,
                    $nuevoSaldo,
                    $concepto
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
                'Bolsón expirado por vencimiento'
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
     */
    private function calcularMinutosSolicitud(TblSolicitudHe $solicitud): int
    {
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
        string $observaciones = null
    ): TblBolsonHist {
        return TblBolsonHist::create([
            'id_bolson_tiempo' => $bolson->id,
            'username' => $bolson->username,
            'accion' => $tipoMovimiento,
            'minutos_afectados' => $minutos,
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => $saldoNuevo,
            'observaciones' => $observaciones
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
     * Obtener estadísticas generales del sistema de bolsones
     */
    public function obtenerEstadisticasGenerales(): array
    {
        return [
            'total_bolsones_activos' => TblBolsonTiempo::where('activo', true)->count(),
            'total_minutos_disponibles' => TblBolsonTiempo::vigentes()->sum('saldo_min'),
            'usuarios_con_bolsones' => TblBolsonTiempo::vigentes()
                ->distinct('username')
                ->count('username'),
            'bolsones_por_vencer_7_dias' => TblBolsonTiempo::vigentes()
                ->where('fecha_vence', '<=', now()->addWeek())
                ->count(),
        ];
    }
}
