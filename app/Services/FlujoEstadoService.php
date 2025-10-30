<?php

namespace App\Services;

use App\Models\TblFlujo;
use App\Models\TblEstado;
use App\Models\TblFlujoEstado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlujoEstadoService
{
    /**
     * Obtener las transiciones disponibles para un estado y flujo específico
     */
    public function obtenerTransicionesDisponibles($flujoId, $estadoOrigenId, $rol = null)
    {
        $query = TblFlujoEstado::where('flujo_id', $flujoId)
            ->where('estado_origen_id', $estadoOrigenId)
            ->where('activo', true);

        if ($rol) {
            $query->where(function($q) use ($rol) {
                $q->where('rol_autorizado', $rol)
                  ->orWhereNull('rol_autorizado');
            });
        }

        return $query->with(['estadoDestino', 'flujo'])
                    ->orderBy('orden')
                    ->get();
    }

    /**
     * Verificar si una transición es válida
     */
    public function validarTransicion($flujoId, $estadoOrigenId, $estadoDestinoId, $rol = null, $solicitud = null)
    {
        $transicion = TblFlujoEstado::where('flujo_id', $flujoId)
            ->where('estado_origen_id', $estadoOrigenId)
            ->where('estado_destino_id', $estadoDestinoId)
            ->where('activo', true)
            ->first();

        if (!$transicion) {
            return [
                'valida' => false,
                'mensaje' => 'Transición no permitida en este flujo'
            ];
        }

        // Verificar rol autorizado
        if ($transicion->rol_autorizado && $rol !== $transicion->rol_autorizado) {
            return [
                'valida' => false,
                'mensaje' => "Solo usuarios con rol '{$transicion->rol_autorizado}' pueden realizar esta acción"
            ];
        }

        // Verificar condición SQL si existe
        if ($transicion->condicion_sql && $solicitud) {
            if (!$this->evaluarCondicionSQL($transicion->condicion_sql, $solicitud)) {
                return [
                    'valida' => false,
                    'mensaje' => 'No se cumplen las condiciones requeridas para esta transición'
                ];
            }
        }

        return [
            'valida' => true,
            'transicion' => $transicion,
            'mensaje' => 'Transición válida'
        ];
    }

    /**
     * Ejecutar una transición de estado
     */
    /**
     * Ejecutar transición de estado para diferentes tipos de modelo
     */
    public function ejecutarTransicionModelo($modeloId, $estadoDestinoId, $usuarioId, $observaciones = null, $tipoModelo = 'TblSolicitudHe')
    {
        try {
            DB::beginTransaction();

            // Obtener el modelo según el tipo
            switch ($tipoModelo) {
                case 'TblSolicitudCompensa':
                    $modelo = \App\Models\TblSolicitudCompensa::find($modeloId);
                    break;
                case 'TblSolicitudHe':
                default:
                    $modelo = TblSolicitudHe::find($modeloId);
                    break;
            }

            if (!$modelo) {
                return [
                    'exitoso' => false,
                    'mensaje' => 'Modelo no encontrado'
                ];
            }

            $estadoAnterior = $modelo->id_estado;

            // Actualizar el estado del modelo
            $modelo->id_estado = $estadoDestinoId;
            $modelo->save();

            // Registrar el seguimiento de la transición
            $this->registrarSeguimientoModelo($modelo, $estadoAnterior, $estadoDestinoId, $usuarioId, $observaciones, $tipoModelo);

            // Ejecutar acciones post-transición si es necesario
            $this->ejecutarAccionesPostTransicion($modelo, $estadoDestinoId);

            DB::commit();

            Log::info("Transición ejecutada correctamente", [
                'modelo_tipo' => $tipoModelo,
                'modelo_id' => $modelo->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoDestinoId,
                'usuario_id' => $usuarioId
            ]);

            return [
                'exitoso' => true,
                'mensaje' => 'Estado actualizado correctamente',
                'modelo' => $modelo
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al ejecutar transición", [
                'error' => $e->getMessage(),
                'modelo_tipo' => $tipoModelo,
                'modelo_id' => $modeloId,
                'estado_destino' => $estadoDestinoId
            ]);

            return [
                'exitoso' => false,
                'mensaje' => 'Error al ejecutar transición: ' . $e->getMessage()
            ];
        }
    }

    public function ejecutarTransicion($solicitud, $estadoDestinoId, $usuarioId, $observaciones = null)
    {
        try {
            DB::beginTransaction();

            $estadoAnterior = $solicitud->id_estado;

            // Actualizar el estado de la solicitud
            $solicitud->id_estado = $estadoDestinoId;
            $solicitud->save();

            // Registrar el seguimiento de la transición
            $this->registrarSeguimiento($solicitud, $estadoAnterior, $estadoDestinoId, $usuarioId, $observaciones);

            // Ejecutar acciones post-transición si es necesario
            $this->ejecutarAccionesPostTransicion($solicitud, $estadoDestinoId);

            DB::commit();

            Log::info("Transición ejecutada correctamente", [
                'solicitud_id' => $solicitud->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoDestinoId,
                'usuario_id' => $usuarioId
            ]);

            return [
                'exitoso' => true,
                'mensaje' => 'Estado actualizado correctamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error en transición de estado", [
                'solicitud_id' => $solicitud->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'exitoso' => false,
                'mensaje' => 'Error al actualizar el estado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener el flujo completo con sus estados y transiciones
     */
    public function obtenerFlujoCompleto($flujoId)
    {
        $flujo = TblFlujo::with([
            'flujosEstados' => function($query) {
                $query->where('activo', true)
                      ->orderBy('orden');
            },
            'flujosEstados.estadoOrigen',
            'flujosEstados.estadoDestino'
        ])->find($flujoId);

        if (!$flujo) {
            return null;
        }

        // Organizar las transiciones para crear un grafo del flujo
        $estados = collect();
        $transiciones = collect();

        foreach ($flujo->flujosEstados as $flujoEstado) {
            $estados->push($flujoEstado->estadoOrigen);
            $estados->push($flujoEstado->estadoDestino);
            $transiciones->push([
                'origen' => $flujoEstado->estadoOrigen,
                'destino' => $flujoEstado->estadoDestino,
                'rol_autorizado' => $flujoEstado->rol_autorizado,
                'condicion_sql' => $flujoEstado->condicion_sql,
                'orden' => $flujoEstado->orden
            ]);
        }

        return [
            'flujo' => $flujo,
            'estados' => $estados->unique('id'),
            'transiciones' => $transiciones,
            'estados_iniciales' => $flujo->estadosIniciales(),
            'estados_finales' => $flujo->estadosFinales()
        ];
    }

    /**
     * Obtener estadísticas de un flujo
     */
    public function obtenerEstadisticasFlujo($flujoId, $fechaInicio = null, $fechaFin = null)
    {
        // Esta función se puede implementar según las necesidades específicas
        // para obtener métricas como tiempo promedio por estado, etc.
        return [];
    }

    /**
     * Evaluar condición SQL de la transición
     */
    private function evaluarCondicionSQL($condicionSQL, $solicitud)
    {
        try {
            // Reemplazar variables en la condición SQL
            $condicion = str_replace([
                'total_minutos',
                'tipo_compensacion',
                'cod_fiscalia'
            ], [
                $solicitud->total_minutos ?? 0,
                $solicitud->tipo_compensacion ?? 0,
                $solicitud->cod_fiscalia ?? 0
            ], $condicionSQL);

            // Evaluar la condición de forma segura
            // En un entorno de producción, considera usar un parser más robusto
            return eval("return $condicion;");

        } catch (\Exception $e) {
            Log::warning("Error evaluando condición SQL: {$condicionSQL}", [
                'error' => $e->getMessage(),
                'solicitud_id' => $solicitud->id ?? null
            ]);
            return false;
        }
    }

    /**
     * Registrar el seguimiento de la transición
     */
    private function registrarSeguimiento($solicitud, $estadoAnterior, $estadoNuevo, $usuarioId, $observaciones)
    {
        try {
            // Verificar si el username existe en tbl_personas
            $usernameValido = $solicitud->username ?? 'sistema';

            // Verificar que el usuario existe en tbl_personas
            $existePersona = DB::table('tbl_personas')
                ->where('username', $usernameValido)
                ->exists();

            if (!$existePersona) {
                // Si no existe, intentar crearlo automáticamente
                $this->crearUsuarioTemporal($usernameValido);

                Log::warning("Usuario {$usernameValido} no existía en tbl_personas, se creó automáticamente", [
                    'solicitud_id' => $solicitud->id,
                    'username_original' => $solicitud->username
                ]);
            }

            // Insertar el seguimiento con username válido
            DB::table('tbl_seguimiento_solicituds')->insert([
                'id_solicitud_he' => $solicitud->id,
                'username' => $usernameValido,
                'id_estado' => $estadoNuevo,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("Seguimiento registrado correctamente", [
                'solicitud_id' => $solicitud->id,
                'username' => $usernameValido,
                'estado_nuevo' => $estadoNuevo
            ]);

        } catch (\Exception $e) {
            Log::error("Error al registrar seguimiento", [
                'solicitud_id' => $solicitud->id,
                'username' => $solicitud->username,
                'error' => $e->getMessage()
            ]);

            // Re-lanzar la excepción para que la transacción se revierta
            throw $e;
        }
    }

    /**
     * Crear un usuario temporal en tbl_personas si no existe
     */
    private function crearUsuarioTemporal($username)
    {
        try {
            // Verificar nuevamente que no existe (por si acaso)
            $existe = DB::table('tbl_personas')->where('username', $username)->exists();
            if ($existe) {
                return;
            }

            DB::table('tbl_personas')->insert([
                'username' => $username,
                'nombre' => 'Usuario Temporal',
                'apellido' => $username,
                'password' => bcrypt('temp_password_' . $username),
                'cod_fiscalia' => 1, // Usar fiscalía por defecto
                'flag_lider' => 0,
                'flag_activo' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("Usuario temporal creado automáticamente", [
                'username' => $username
            ]);

        } catch (\Exception $e) {
            Log::error("Error al crear usuario temporal", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            // Si no se puede crear el usuario, no fallar toda la transacción
            // Solo loggear el error
        }
    }

    private function registrarSeguimientoModelo($modelo, $estadoAnterior, $estadoNuevo, $usuarioId, $observaciones, $tipoModelo)
    {
        try {
            if ($tipoModelo === 'TblSolicitudCompensa') {
                // Para compensaciones, podríamos usar una tabla específica o registrar en logs
                Log::info("Seguimiento compensación", [
                    'id_solicitud_compensa' => $modelo->id,
                    'username' => $modelo->username ?? 'sistema',
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $estadoNuevo,
                    'usuario_transicion' => $usuarioId,
                    'observaciones' => $observaciones
                ]);
            } else {
                // Para HE usar el método original
                $this->registrarSeguimiento($modelo, $estadoAnterior, $estadoNuevo, $usuarioId, $observaciones);
            }
        } catch (\Exception $e) {
            Log::error("Error al registrar seguimiento", [
                'tipo_modelo' => $tipoModelo,
                'modelo_id' => $modelo->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ejecutar acciones específicas después de una transición
     */
    private function ejecutarAccionesPostTransicion($solicitud, $estadoDestinoId)
    {
        $estado = TblEstado::find($estadoDestinoId);

        if (!$estado) {
            return;
        }

        // Ejecutar acciones según el tipo de acción del estado
        switch ($estado->tipo_accion) {
            case 'SUMA':
                // Agregar tiempo al bolsón (HE aprobadas o compensaciones rechazadas)
                $this->agregarTiempoAlBolson($solicitud);
                break;

            case 'RESTA':
                // Descontar tiempo del bolsón (para compensaciones solicitadas)
                $this->descontarTiempoDelBolson($solicitud);
                break;

            case 'COMPENSACION':
                // Procesar compensación (método legacy)
                $this->procesarCompensacion($solicitud);
                break;

            case 'PAGO':
                // Procesar pago
                $this->procesarPago($solicitud);
                break;

            default:
                // No hacer nada para tipo 'NINGUNA'
                break;
        }
    }

    /**
     * Descontar tiempo del bolsón cuando una compensación es aprobada
     */
    private function descontarTiempoDelBolson($solicitud)
    {
        try {
            // Si es una solicitud de compensación, descontamos del bolsón
            if ($solicitud instanceof \App\Models\TblSolicitudCompensa) {
                $bolsonService = new BolsonService();

                $resultado = $bolsonService->descontarMinutos(
                    $solicitud->username,
                    $solicitud->minutos_aprobados ?? $solicitud->minutos_solicitados,
                    "Compensación aprobada automáticamente - Solicitud #{$solicitud->id}"
                );

                if ($resultado['success']) {
                    Log::info("Tiempo descontado automáticamente del bolsón", [
                        'solicitud_compensacion_id' => $solicitud->id,
                        'username' => $solicitud->username,
                        'minutos_descontados' => $solicitud->minutos_aprobados ?? $solicitud->minutos_solicitados,
                        'bolsones_afectados' => count($resultado['bolsones_afectados'])
                    ]);
                } else {
                    Log::error("Error al descontar automáticamente del bolsón", [
                        'solicitud_compensacion_id' => $solicitud->id,
                        'error' => $resultado['mensaje']
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Excepción al descontar tiempo del bolsón", [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Agregar tiempo al bolsón cuando una HE es aprobada
     */
    private function agregarTiempoAlBolson($solicitud)
    {
        try {
            $bolsonService = app(\App\Services\BolsonService::class);

            // Si es una HE, usar el método existente
            if ($solicitud instanceof \App\Models\TblSolicitudHe) {
                $bolsonService->procesarSolicitudHeAprobada($solicitud);
            }
            // Si es una compensación rechazada, devolver minutos al bolsón
            elseif ($solicitud instanceof \App\Models\TblSolicitudCompensa) {
                $this->devolverMinutosAlBolson($solicitud, $bolsonService);
            }
        } catch (\Exception $e) {
            Log::error("Error al agregar tiempo al bolsón", [
                'solicitud_id' => $solicitud->id,
                'tipo' => get_class($solicitud),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Devolver minutos al bolsón cuando se rechaza una compensación
     */
    private function devolverMinutosAlBolson($compensacion, $bolsonService)
    {
        $minutosADevolver = $compensacion->minutos_aprobados ?? $compensacion->minutos_solicitados;

        // Crear un bolsón de "devolución"
        $resultado = $bolsonService->crearBolsonDevolución(
            $compensacion->username,
            $minutosADevolver,
            "Devolución por compensación rechazada - Solicitud #{$compensacion->id}"
        );

        Log::info("Minutos devueltos al bolsón por compensación rechazada", [
            'compensacion_id' => $compensacion->id,
            'username' => $compensacion->username,
            'minutos_devueltos' => $minutosADevolver,
            'bolson_creado' => $resultado['success'] ?? false
        ]);
    }

    /**
     * Crear bolsón pendiente cuando se ingresa una solicitud HE
     */
    public function crearBolsonPendienteParaSolicitud($solicitud)
    {
        // Usar el BolsonService para crear bolsón pendiente
        if (class_exists(\App\Services\BolsonService::class)) {
            $bolsonService = app(\App\Services\BolsonService::class);
            return $bolsonService->crearBolsonPendiente($solicitud);
        }
        return null;
    }

    /**
     * Procesar compensación de tiempo
     */
    private function procesarCompensacion($solicitud)
    {
        // Implementar lógica de compensación
        Log::info("Procesando compensación para solicitud {$solicitud->id}");
    }

    /**
     * Procesar pago de horas extras
     */
    private function procesarPago($solicitud)
    {
        // Implementar lógica de pago
        Log::info("Procesando pago para solicitud {$solicitud->id}");
    }

    /**
     * Ejecutar múltiples transiciones de estado (aprobaciones masivas)
     */
    public function ejecutarTransicionesMultiples(array $solicitudesIds, $estadoDestinoId, $usuarioId, $observaciones = null)
    {
        try {
            DB::beginTransaction();

            $resultado = [
                'exitoso' => true,
                'procesadas' => 0,
                'errores' => [],
                'solicitudes_procesadas' => [],
                'bolsones_creados' => [],
                'mensaje' => ''
            ];

            // Obtener solicitudes válidas
            $solicitudes = \App\Models\TblSolicitudHe::whereIn('id', $solicitudesIds)->get();

            if ($solicitudes->isEmpty()) {
                DB::rollBack();
                return [
                    'exitoso' => false,
                    'mensaje' => 'No se encontraron solicitudes válidas'
                ];
            }

            foreach ($solicitudes as $solicitud) {
                try {
                    // Ejecutar transición individual usando el método existente
                    $resultadoIndividual = $this->ejecutarTransicion(
                        $solicitud,
                        $estadoDestinoId,
                        $usuarioId,
                        $observaciones ?? "Aprobación masiva - {$solicitudes->count()} solicitudes"
                    );

                    if ($resultadoIndividual['exitoso']) {
                        $resultado['procesadas']++;
                        $resultado['solicitudes_procesadas'][] = [
                            'id' => $solicitud->id,
                            'username' => $solicitud->username,
                            'total_min' => $solicitud->total_min,
                            'estado_anterior' => $solicitud->getOriginal('id_estado'),
                            'estado_nuevo' => $estadoDestinoId
                        ];

                        // Si es una aprobación, verificar si se creó bolsón
                        if ($estadoDestinoId == 3) { // APROBADO_JEFE
                            $bolsonCreado = \App\Models\TblBolsonTiempo::where('id_solicitud_he', $solicitud->id)
                                ->where('estado', 'DISPONIBLE')
                                ->first();

                            if ($bolsonCreado) {
                                $resultado['bolsones_creados'][] = [
                                    'bolson_id' => $bolsonCreado->id,
                                    'solicitud_id' => $solicitud->id,
                                    'minutos' => $bolsonCreado->minutos,
                                    'username' => $bolsonCreado->username
                                ];
                            }
                        }
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

            // Determinar si la operación fue exitosa
            $resultado['exitoso'] = $resultado['procesadas'] > 0;

            if ($resultado['exitoso']) {
                $totalBolsones = count($resultado['bolsones_creados']);
                $totalMinutos = array_sum(array_column($resultado['bolsones_creados'], 'minutos'));

                $resultado['mensaje'] = "✅ Procesadas {$resultado['procesadas']} solicitudes. " .
                                      ($totalBolsones > 0 ? "Creados {$totalBolsones} bolsones con {$totalMinutos} min totales." : "");

                DB::commit();

                Log::info("Aprobación masiva exitosa", [
                    'procesadas' => $resultado['procesadas'],
                    'bolsones_creados' => $totalBolsones,
                    'minutos_totales' => $totalMinutos,
                    'usuario_id' => $usuarioId
                ]);
            } else {
                DB::rollBack();
                $resultado['mensaje'] = "❌ No se pudo procesar ninguna solicitud. " . count($resultado['errores']) . " errores.";
            }

            return $resultado;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en aprobación masiva", [
                'solicitudes_ids' => $solicitudesIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
     * Crear una solicitud de prueba para el flujo simple
     */
    public function crearSolicitudPrueba($username = 'test_user', $totalMinutos = 120)
    {
        try {
            DB::beginTransaction();

            $solicitud = \App\Models\TblSolicitudHe::create([
                'username' => $username,
                'cod_fiscalia' => 1,
                'id_tipo_trabajo' => 1,
                'fecha' => now()->format('Y-m-d'),
                'hrs_inicial' => '09:00:00',
                'hrs_final' => '11:00:00',
                'id_estado' => 1, // INGRESADO
                'id_tipo_compensacion' => 1, // HE_COMPENSACION
                'min_reales' => $totalMinutos,
                'min_25' => 0,
                'min_50' => 0,
                'total_min' => $totalMinutos,
            ]);

            // Registrar el seguimiento inicial
            $this->registrarSeguimiento($solicitud, null, 1, null, 'Solicitud creada automáticamente para prueba');

            // Crear bolsón pendiente automáticamente
            $bolsonCreado = $this->crearBolsonPendienteParaSolicitud($solicitud);

            DB::commit();

            Log::info("Solicitud de prueba creada con bolsón pendiente", [
                'solicitud_id' => $solicitud->id,
                'username' => $username,
                'total_minutos' => $totalMinutos,
                'bolson_id' => $bolsonCreado?->id,
                'bolson_estado' => $bolsonCreado?->estado
            ]);

            return [
                'exitoso' => true,
                'solicitud' => $solicitud,
                'bolson' => $bolsonCreado,
                'mensaje' => 'Solicitud de prueba creada correctamente con bolsón pendiente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creando solicitud de prueba", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'exitoso' => false,
                'mensaje' => 'Error al crear solicitud de prueba: ' . $e->getMessage()
            ];
        }
    }
}
