<?php

namespace App\Http\Controllers;

use App\Models\TblSolicitudHe;
use App\Models\TblFlujo;
use App\Services\FlujoEstadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    protected $flujoService;

    public function __construct(FlujoEstadoService $flujoService)
    {
        $this->flujoService = $flujoService;
    }

    /**
     * Obtener las transiciones disponibles para una solicitud
     */
    public function obtenerTransicionesDisponibles($solicitudId)
    {
        try {
            $solicitud = TblSolicitudHe::findOrFail($solicitudId);
            $rol = Auth::user()->id_rol ?? Auth::user()->rol ?? null; // preferir id_rol

            $transiciones = $solicitud->transicionesDisponibles($rol);

            return response()->json([
                'success' => true,
                'data' => [
                    'solicitud_id' => $solicitud->id,
                    'estado_actual' => $solicitud->estado->gls_estado,
                    'transiciones_disponibles' => $transiciones->map(function($transicion) {
                        return [
                            'estado_destino_id' => $transicion->estado_destino_id,
                            'estado_destino' => $transicion->estadoDestino->gls_estado,
                            'rol_autorizado' => $transicion->rol_autorizado,
                            'condicion_sql' => $transicion->condicion_sql,
                            'orden' => $transicion->orden
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener transiciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecutar una transición de estado
     */
    public function ejecutarTransicion(Request $request, $solicitudId)
    {
        $request->validate([
            'estado_destino_id' => 'required|integer|exists:tbl_estados,id',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        try {
            $solicitud = TblSolicitudHe::findOrFail($solicitudId);
            $rol = Auth::user()->id_rol ?? Auth::user()->rol ?? null;

            // Validar la transición
            $validacion = $solicitud->puedeTransicionarA(
                $request->estado_destino_id,
                $rol
            );

            if (!$validacion['valida']) {
                return response()->json([
                    'success' => false,
                    'message' => $validacion['mensaje']
                ], 400);
            }

            // Ejecutar la transición
            $resultado = $solicitud->transicionarA(
                $request->estado_destino_id,
                Auth::id(),
                $request->observaciones
            );

            if ($resultado['exitoso']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['mensaje'],
                    'data' => [
                        'solicitud_id' => $solicitud->id,
                        'nuevo_estado' => $solicitud->fresh()->estado->gls_estado
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['mensaje']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar transición: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el historial de una solicitud
     */
    public function obtenerHistorial($solicitudId)
    {
        try {
            $solicitud = TblSolicitudHe::findOrFail($solicitudId);
            $historial = $solicitud->historialTransiciones();

            return response()->json([
                'success' => true,
                'data' => [
                    'solicitud_id' => $solicitud->id,
                    'estado_actual' => $solicitud->estado->gls_estado,
                    'historial' => $historial->map(function($seguimiento) {
                        return [
                            'id' => $seguimiento->id,
                            'estado_anterior' => $seguimiento->estadoAnterior->gls_estado ?? 'Inicio',
                            'estado_nuevo' => $seguimiento->estadoNuevo->gls_estado ?? 'N/A',
                            'usuario' => $seguimiento->nombre_usuario,
                            'observaciones' => $seguimiento->observaciones,
                            'fecha_cambio' => $seguimiento->fecha_cambio->format('d/m/Y H:i:s'),
                            'descripcion' => $seguimiento->descripcion_cambio
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el flujo completo de un tipo específico
     */
    public function obtenerFlujo($flujoId)
    {
        try {
            $flujoCompleto = $this->flujoService->obtenerFlujoCompleto($flujoId);

            if (!$flujoCompleto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Flujo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $flujoCompleto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener flujo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los flujos disponibles
     */
    public function obtenerFlujos()
    {
        try {
            $flujos = TblFlujo::where('activo', true)->get();

            return response()->json([
                'success' => true,
                'data' => $flujos->map(function($flujo) {
                    return [
                        'id' => $flujo->id,
                        'nombre' => $flujo->nombre,
                        'descripcion' => $flujo->descripcion,
                        'activo' => $flujo->activo
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener flujos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Página de prueba del sistema de workflow
     */
    public function demo()
    {
        $solicitudes = TblSolicitudHe::with(['estado', 'user', 'tipoCompensacion'])
            ->limit(10)
            ->get();

        $flujos = TblFlujo::where('activo', true)->get();

        return view('workflow.demo', compact('solicitudes', 'flujos'));
    }

    /**
     * Crear una solicitud de prueba
     */
    public function crearSolicitudPrueba(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string|max:100',
            'total_minutos' => 'nullable|integer|min:1|max:1440'
        ]);

        $username = $request->username ?? 'test_user_' . rand(1000, 9999);
        $totalMinutos = $request->total_minutos ?? rand(60, 480);

        try {
            $resultado = $this->flujoService->crearSolicitudPrueba($username, $totalMinutos);

            if ($resultado['exitoso']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['mensaje'],
                    'data' => [
                        'solicitud_id' => $resultado['solicitud']->id,
                        'username' => $resultado['solicitud']->username,
                        'total_minutos' => $resultado['solicitud']->total_min,
                        'estado_actual' => $resultado['solicitud']->estado->gls_estado ?? 'INGRESADO'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['mensaje']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}
