<?php

namespace App\Services;

use App\Models\TblFlujo;
use App\Models\TblEstado;
use App\Models\TblFlujoEstado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User; // añadir import si no existe


class FlujoEstadoService
{

    public function estadoInicial()
    {
        return $this->hasOneThrough(
            TblEstado::class,
            TblFlujoEstado::class,
            'flujo_id',            // FK en tbl_flujos_estados
            'id',                  // PK en tbl_estados
            'id',                  // PK en tbl_flujos
            'estado_origen_id'     // campo en tbl_flujos_estados
        )->whereNotIn('tbl_estados.id', function ($q) {
            $q->select('estado_destino_id')
            ->from('tbl_flujos_estados')
            ->whereColumn('tbl_flujos_estados.flujo_id', 'tbl_flujos.id');
        });
    }

    // NUEVO: resolver flujo_id a partir del registro TblEstado (soporta campo string 'flujo')
    private function resolveFlujoIdFromEstado($estado)
    {
        if (!$estado) return null;

        // Si ya existe flujo_id explícito (por si alguna vez se agrega)
        if (!empty($estado->flujo_id)) {
            return $estado->flujo_id;
        }

        // Si existe campo string 'flujo', buscar TblFlujo por codigo o slug
        if (!empty($estado->flujo)) {
            $codigo = trim((string)$estado->flujo);

            $flujo = TblFlujo::where('codigo', $codigo)
                      ->orWhere('slug', $codigo)
                      ->first();

            if ($flujo) {
                return $flujo->id;
            }

            // Mapas comunes (ajustar según seeders)
            $map = [
                'COMPENSACION' => 'HE_COMPENSACION',
                'DINERO' => 'HE_DINERO',
                'TIEMPO' => 'HE_COMPENSACION',
                'AMBOS' => null
            ];

            if (isset($map[$codigo]) && $map[$codigo]) {
                $flujo = TblFlujo::where('codigo', $map[$codigo])->first();
                if ($flujo) return $flujo->id;
            }
        }

        return null;
    }

    function obtenerSiguientesEstados(int $flujoId, int $estadoActualId, ?string $rol = null)
    {
        $query = DB::table('tbl_flujos_estados as fe')
            ->join('tbl_estados as e', 'e.id', '=', 'fe.estado_destino_id')
            ->where('fe.flujo_id', $flujoId)
            ->where('fe.estado_origen_id', $estadoActualId)
            ->where('fe.activo', true)
            ->select('e.id as id_estado', 'e.codigo', 'e.descripcion', 'fe.rol_autorizado', 'fe.orden');

        if ($rol) {
            $query->where(function ($q) use ($rol) {
                $q->whereNull('fe.rol_autorizado')
                ->orWhere('fe.rol_autorizado', $rol);
            });
        }

        return $query->orderBy('fe.orden')->get();
    }

    // NUEVO: obtener transiciones desde tbl_flujos_estados independientemente del flujo asociado al estado
    public function obtenerSiguientesTransicionesPorEstadoOrigen(int $estadoOrigenId, ?string $rol = null)
    {
        $query = DB::table('tbl_flujos_estados as fe')
            ->join('tbl_estados as e', 'e.id', '=', 'fe.estado_destino_id')
            ->where('fe.estado_origen_id', $estadoOrigenId)
            ->where('fe.activo', true)
            ->select('fe.*', 'e.codigo as estado_codigo', 'e.descripcion as estado_descripcion')
            ->orderBy('fe.flujo_id')
            ->orderBy('fe.orden');

        if ($rol) {
            $query->where(function ($q) use ($rol) {
                $q->whereNull('fe.rol_autorizado')
                  ->orWhere('fe.rol_autorizado', $rol);
            });
        }

        return $query->get();
    }

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
        try {
            // Obtener estado actual y resolver flujo desde el estado (usa string 'flujo' si aplica)
            $estadoActual = TblEstado::find($solicitud->id_estado);
            $flujoId = $this->resolveFlujoIdFromEstado($estadoActual);

            // Si el estado no tiene flujo, intentar buscar un flujo con código/slug relacionado a PAGO
            if (!$flujoId) {
                $flujo = TblFlujo::where('codigo', 'PAGO')
                    ->orWhere('slug', 'pago')
                    ->first();
                $flujoId = $flujo->id ?? null;
            }

            if (!$flujoId) {
                Log::warning("No se encontró flujo para procesar pago", [
                    'solicitud_id' => $solicitud->id,
                    'estado_actual' => $solicitud->id_estado
                ]);
                return;
            }

            // Obtener transiciones disponibles desde el estado actual según el flujo
            $transiciones = $this->obtenerTransicionesDisponibles($flujoId, $solicitud->id_estado);

            if ($transiciones->isEmpty()) {
                Log::info("No hay transiciones configuradas en flujo PAGO desde este estado", [
                    'flujo_id' => $flujoId,
                    'estado_origen' => $solicitud->id_estado,
                    'solicitud_id' => $solicitud->id
                ]);
                return;
            }

            // Evaluar y ejecutar la primera transición válida (respeta condiciones y rol)
            foreach ($transiciones as $transicion) {
                try {
                    $validacion = $this->validarTransicion(
                        $flujoId,
                        $solicitud->id_estado,
                        $transicion->estado_destino_id,
                        null,
                        $solicitud
                    );

                    if (!empty($validacion['valida'])) {
                        // Ejecutar la transición (usar null o system user si corresponde)
                        $usuarioId = auth()->id() ?? null;
                        $resultado = $this->ejecutarTransicion(
                            $solicitud,
                            $transicion->estado_destino_id,
                            $usuarioId,
                            "Transición automática por flujo PAGO (flujo_id: {$flujoId}, transicion_id: {$transicion->id})"
                        );

                        if (!empty($resultado['exitoso'])) {
                            Log::info("Transición de flujo PAGO ejecutada", [

                                'flujo_id' => $flujoId,
                                'transicion_id' => $transicion->id,
                                'estado_destino' => $transicion->estado_destino_id
                            ]);
                        } else {
                            Log::error("Fallo al ejecutar transición de flujo PAGO", [
                                'solicitud_id' => $solicitud->id,
                                'transicion_id' => $transicion->id,
                                'error' => $resultado['mensaje'] ?? 'sin mensaje'
                            ]);
                        }

                        // Ejecutar solo la primera transición aplicable (evita ramas múltiples automáticas)
                        return;
                    }
                } catch (\Exception $e) {
                    Log::error("Error evaluando/ejecutando transición de flujo PAGO", [
                        'solicitud_id' => $solicitud->id,
                        'transicion_id' => $transicion->id ?? null,
                        'error' => $e->getMessage()
                    ]);
                    // continuar con la siguiente transición si existe
                }
            }

            // Si llega aquí, no se encontró ninguna transición válida
            Log::info("Ninguna transición válida encontrada en flujo PAGO para la solicitud", [
                'solicitud_id' => $solicitud->id,
                'flujo_id' => $flujoId
            ]);

        } catch (\Exception $e) {
            Log::error("Excepción en procesarPago", [
                'solicitud_id' => $solicitud->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ejecutar múltiples transiciones de estado (aprobaciones masivas)
     */
    public function ejecutarTransicionesMultiples(array $solicitudesIds, $estadoDestinoId = null, $usuarioId = null, $observaciones = null)
    {
        // LOG: ver qué llega realmente al servicio
        Log::info('FlujoEstadoService::ejecutarTransicionesMultiples invocado', [
            'param_solicitudesIds' => $solicitudesIds,
            'usuarioId_param' => $usuarioId,
            'observaciones' => $observaciones
        ]);

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
                    $estadoDestinoAUsar = $estadoDestinoId;

                    // resolver rol del usuario: preferir auth()->user()->rol/role, luego intentar mapear id_rol a nombre
                    $rolUsuario = null;
                    try {
                        $authUser = auth()->user();
                        if ($authUser) {
                            $rolUsuario = $authUser->rol ?? $authUser->role ?? null;

                            if (empty($rolUsuario) && isset($authUser->id_rol)) {
                                try {
                                    if (DB::getSchemaBuilder()->hasTable('tbl_roles')) {
                                        $roleRow = DB::table('tbl_roles')->where('id', $authUser->id_rol)->first();
                                        $rolUsuario = $roleRow->codigo ?? $roleRow->nombre ?? null;
                            }
                        } catch (\Exception $e) {
                            // ignorar
                        }
                    }
                }

                if (empty($rolUsuario) && $usuarioId) {
                    try {
                        $userModel = new User();
                        if (DB::getSchemaBuilder()->hasTable($userModel->getTable())) {
                            $u = User::where('id', $usuarioId)->orWhere('username', $usuarioId)->first();
                            $rolUsuario = $u->rol ?? $u->role ?? $u->id_rol ?? null;
                        } else {
                            if (DB::getSchemaBuilder()->hasTable('tbl_personas')) {
                                $p = DB::table('tbl_personas')->where('id', $usuarioId)->orWhere('username', $usuarioId)->first();
                                $rolUsuario = $p->rol ?? $p->id_rol ?? null;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('No se pudo resolver usuario para rol: ' . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error resolviendo rol usuario: ' . $e->getMessage());
                $rolUsuario = null;
            }

            // FORZAR ROL TEMPORALMENTE PARA PRUEBAS
            if (empty($rolUsuario)) {
                $rolUsuario = 'JEFE';
            }

            // MAPEO TEMPORAL: si se obtuvo un id numérico de rol (p.ej. id_rol = 1), mapearlo a nombre
            $mapIdRolToNombre = [
                1 => 'JEFE',
                2 => 'RRHH',
                3 => 'DER',
                // añadir más mapeos aquí si los conoces
            ];
            if (is_numeric($rolUsuario)) {
                $rolInt = (int) $rolUsuario;
                if (isset($mapIdRolToNombre[$rolInt])) {
                    $rolUsuario = $mapIdRolToNombre[$rolInt];
                } else {
                    // si no hay mapeo, dejar el valor original (para facilitar debug)
                    $rolUsuario = (string) $rolUsuario;
                }
            }

            Log::info('FlujoEstadoService: rol resuelto', [
                'solicitud_id' => $solicitud->id ?? null,
                'usuarioId_param' => $usuarioId,
                'rol_resuelto' => $rolUsuario
            ]);

                    // Si no se pasó estado destino, determinar según las filas en tbl_flujos_estados
                    if ($estadoDestinoAUsar === null) {
                        // obtener transiciones según estado origen y rol resuelto
                        $transiciones = $this->obtenerSiguientesTransicionesPorEstadoOrigen($solicitud->id_estado, $rolUsuario);

                        foreach ($transiciones as $transicion) {
                            $validacion = $this->validarTransicion(
                                $transicion->flujo_id,
                                $solicitud->id_estado,
                                $transicion->estado_destino_id,
                                $rolUsuario,
                                $solicitud
                            );

                            if (!empty($validacion['valida'])) {
                                $estadoDestinoAUsar = $transicion->estado_destino_id;
                                // ...log si necesario...
                                break;
                            }
                        }
                    }

                    if (!$estadoDestinoAUsar) {
                        $resultado['errores'][] = [
                            'solicitud_id' => $solicitud->id,
                            'error' => 'No se pudo determinar estado destino para la solicitud'
                        ];
                        continue;
                    }

                    $resultadoIndividual = $this->ejecutarTransicion(
                        $solicitud,
                        $estadoDestinoAUsar,
                        $usuarioId,
                        $observaciones ?? "Aprobación masiva - {$solicitudes->count()} solicitudes",
                        false // evitar anidar transacciones
                    );

                    if ($resultadoIndividual['exitoso']) {
                        $resultado['procesadas']++;
                        $resultado['solicitudes_procesadas'][] = [
                            'id' => $solicitud->id,
                            'username' => $solicitud->username ?? null,
                            'total_min' => $solicitud->total_min ?? null,
                            'estado_anterior' => $solicitud->getOriginal('id_estado'),
                            'estado_nuevo' => $estadoDestinoAUsar
                        ];

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
                $totalBolsones = count($resultado['bolsones_creados']);
                $totalMinutos = array_sum(array_column($resultado['bolsones_creados'], 'minutos'));

                $resultado['mensaje'] = "✅ Procesadas {$resultado['procesadas']} solicitudes. " .
                                      ($totalBolsones > 0 ? "Creados {$totalBolsones} bolsones con {$totalMinutos} min totales." : "");

                DB::commit();
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
