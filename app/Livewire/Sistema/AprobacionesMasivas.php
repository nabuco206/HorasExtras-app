<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Services\FlujoEstadoService;
use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AprobacionesMasivas extends Component
{
    public $tipos_trabajo = [];
    public $estados = [];
    public $solicitudes = [];
    public $seleccionados = [];
    public $selectAll = false;
    public $mostrarSoloPendientes = true;
    public $filtroBusqueda = '';
    public $filtroEstado = 1; // Pendientes por defecto
    public $estadisticas = null;

    // Para mostrar resultados
    public $ultimaOperacion = null;
    public $mostrarResultados = false;

    public function mount()
    {
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
        $this->actualizarEstadisticas();
        $this->cargarSolicitudes();
    }

    /**
     * Query base para listar (se puede sobreescribir en hijos).
     * Muestra solo solicitudes con id_tipo_compensacion = 0.
     */
    protected function baseQuery()
    {
        return TblSolicitudHe::with(['tipoTrabajo', 'estado'])
            ->where('id_tipo_compensacion', 0)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Query base para estadísticas (se puede sobreescribir en hijos).
     * Limita a id_tipo_compensacion = 0.
     */
    protected function statsQuery()
    {
        return TblSolicitudHe::query()->where('id_tipo_compensacion', 0);
       
    }

    public function cargarSolicitudes()
    {
        // usar query base (heredable)
        $query = $this->baseQuery();

        // -- eliminar aquí cualquier filtro fijo de tipo de compensación --
        // Filtro por estado
        if ($this->filtroEstado) {
            $query->where('id_estado', $this->filtroEstado);
        }

        // Filtro de búsqueda
        if ($this->filtroBusqueda) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->filtroBusqueda . '%')
                ->orWhere('id', 'like', '%' . $this->filtroBusqueda . '%');
            });
        }

        $this->solicitudes = $query->get();
        $this->reset(['seleccionados', 'selectAll']);
    }

    public function updatedMostrarSoloPendientes()
    {
        $this->cargarSolicitudes();
    }

    public function actualizarEstadisticas()
    {
        // usar statsQuery() que puede añadir filtros (ej: id_tipo_compensacion)
        $query = $this->statsQuery();

        $pendientes = (clone $query)->where('id_estado', 1)->count();
        $aprobadas = (clone $query)->where('id_estado', 3)->count();
        $rechazadas = (clone $query)->where('id_estado', 4)->count();
        $compensacionSolicitada = (clone $query)->where('id_estado', 5)->count();
        $compensacionAprobada = (clone $query)->where('id_estado', 6)->count();

        $minutosAprobados = (clone $query)->where('id_estado', 3)->sum('total_min');
        $minutosPendientes = (clone $query)->where('id_estado', 1)->sum('total_min');
        $minutosRechazados = (clone $query)->where('id_estado', 4)->sum('total_min');

        $totalSolicitudes = $pendientes + $aprobadas + $rechazadas + $compensacionSolicitada + $compensacionAprobada;
        $totalProcesadas = $aprobadas + $rechazadas;

        $this->estadisticas = [
            'total_solicitudes' => $totalSolicitudes,
            'pendientes' => $pendientes,
            'aprobadas' => $aprobadas,
            'rechazadas' => $rechazadas,
            'compensacion_solicitada' => $compensacionSolicitada,
            'compensacion_aprobada' => $compensacionAprobada,
            'minutos_aprobados_total' => $minutosAprobados ?? 0,
            'minutos_pendientes_total' => $minutosPendientes ?? 0,
            'minutos_rechazados_total' => $minutosRechazados ?? 0,
            'porcentaje_aprobacion' => $totalProcesadas > 0 ? round(($aprobadas / $totalProcesadas) * 100, 1) : 0
        ];
    }

    public function seleccionarTodas()
    {
        $this->seleccionados = $this->solicitudes->pluck('id')->toArray();
        $this->selectAll = true;
    }

    public function deseleccionarTodas()
    {
        $this->seleccionados = [];
        $this->selectAll = false;
    }

    public function updated($property)
    {
        if (in_array($property, ['filtroEstado', 'filtroBusqueda', 'mostrarSoloPendientes'])) {
            $this->cargarSolicitudes();
            $this->deseleccionarTodas();
        }
    }

    public function aprobarSeleccionados()
    {
        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debe seleccionar al menos una solicitud');
            return;
        }

        try {
            $flujoService = app(FlujoEstadoService::class);

            // Obtener usuario autenticado
            $usuarioId = Auth::id() ?? 1; // Fallback a 1 si no hay usuario autenticado

            Log::info("Iniciando aprobación masiva", [
                'seleccionados' => $this->seleccionados,
                'usuario_id' => $usuarioId,
                'total_seleccionados' => count($this->seleccionados)
            ]);

            // Usar el nuevo método de aprobaciones masivas
            $resultado = $flujoService->ejecutarTransicionesMultiples(
                $this->seleccionados,
                3, // APROBADO_JEFE
                $usuarioId,
                'Aprobación masiva desde interfaz'
            );

            Log::info("Resultado de aprobación masiva", [
                'exitoso' => $resultado['exitoso'],
                'procesadas' => $resultado['procesadas'] ?? 0,
                'bolsones_creados' => count($resultado['bolsones_creados'] ?? []),
                'mensaje' => $resultado['mensaje'] ?? 'Sin mensaje'
            ]);

            if ($resultado['exitoso']) {
                $this->ultimaOperacion = $resultado;
                $this->mostrarResultados = true;

                session()->flash('mensaje', $resultado['mensaje']);

                // Recargar datos
                $this->cargarSolicitudes();
                $this->actualizarEstadisticas();

                Log::info("Aprobación masiva completada desde interfaz", [
                    'procesadas' => $resultado['procesadas'],
                    'bolsones_creados' => count($resultado['bolsones_creados']),
                    'seleccionados' => $this->seleccionados
                ]);

            } else {
                session()->flash('error', $resultado['mensaje']);
                Log::warning("Aprobación masiva falló", [
                    'resultado' => $resultado,
                    'seleccionados' => $this->seleccionados
                ]);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar aprobaciones: ' . $e->getMessage());
            Log::error("Error en aprobación masiva desde interfaz", [
                'error' => $e->getMessage(),
                'seleccionados' => $this->seleccionados
            ]);
        }
    }

    public function rechazarSeleccionados()
    {
        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debe seleccionar al menos una solicitud');
            return;
        }

        try {
            $flujoService = app(FlujoEstadoService::class);
            $usuarioId = Auth::id() ?? 1;

            Log::info("Iniciando rechazo masivo", [
                'seleccionados' => $this->seleccionados,
                'usuario_id' => $usuarioId,
                'total_seleccionados' => count($this->seleccionados)
            ]);

            // Verificar que existe un estado de rechazo (ID 4)
            $estadoRechazo = TblEstado::find(4);
            if (!$estadoRechazo) {
                session()->flash('error', 'No se encontró el estado de rechazo en el sistema');
                return;
            }

            // Usar el método de transiciones múltiples para rechazar
            $resultado = $flujoService->ejecutarTransicionesMultiples(
                $this->seleccionados,
                4, // RECHAZADO_JEFE
                $usuarioId,
                'Rechazo masivo desde interfaz'
            );

            Log::info("Resultado de rechazo masivo", [
                'exitoso' => $resultado['exitoso'],
                'procesadas' => $resultado['procesadas'] ?? 0,
                'mensaje' => $resultado['mensaje'] ?? 'Sin mensaje'
            ]);

            if ($resultado['exitoso']) {
                $this->ultimaOperacion = $resultado;
                $this->mostrarResultados = true;

                session()->flash('mensaje', $resultado['mensaje']);

                // Recargar datos
                $this->cargarSolicitudes();
                $this->actualizarEstadisticas();

                Log::info("Rechazo masivo completado desde interfaz", [
                    'procesadas' => $resultado['procesadas'],
                    'seleccionados' => $this->seleccionados
                ]);

            } else {
                session()->flash('error', $resultado['mensaje']);
                Log::warning("Rechazo masivo falló", [
                    'resultado' => $resultado,
                    'seleccionados' => $this->seleccionados
                ]);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar rechazos: ' . $e->getMessage());
            Log::error("Error en rechazo masivo desde interfaz", [
                'error' => $e->getMessage(),
                'seleccionados' => $this->seleccionados
            ]);
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->seleccionados = $this->solicitudes->pluck('id')->toArray();
        } else {
            $this->seleccionados = [];
        }
    }

    public function updatedSeleccionados()
    {
        $this->selectAll = count($this->seleccionados) === count($this->solicitudes) && count($this->solicitudes) > 0;
    }

    public function cerrarResultados()
    {
        $this->mostrarResultados = false;
        $this->ultimaOperacion = null;
    }

    // Método de prueba para debuggear desde la interfaz
    public function probarAprobacion()
    {
        Log::info("Método probarAprobacion llamado");

        try {
            $solicitudesPendientes = TblSolicitudHe::where('id_estado', 1)->take(2)->pluck('id')->toArray();

            Log::info("Solicitudes encontradas para prueba", ['ids' => $solicitudesPendientes]);

            if (empty($solicitudesPendientes)) {
                session()->flash('warning', 'No hay HE pendientes para probar');
                return;
            }

            $flujoService = app(FlujoEstadoService::class);
            $resultado = $flujoService->ejecutarTransicionesMultiples(
                $solicitudesPendientes,
                3,
                Auth::id() ?? 1,
                'Prueba desde interfaz web'
            );

            Log::info("Resultado de prueba", ['resultado' => $resultado]);

            if ($resultado['exitoso']) {
                session()->flash('mensaje', 'Prueba exitosa: ' . $resultado['mensaje']);
                $this->cargarSolicitudes();
                $this->actualizarEstadisticas();
            } else {
                session()->flash('error', 'Prueba falló: ' . $resultado['mensaje']);
            }

        } catch (\Exception $e) {
            Log::error("Error en prueba de aprobación", ['error' => $e->getMessage()]);
            session()->flash('error', 'Error en prueba: ' . $e->getMessage());
        }
    }

    /**
     * Exportar datos seleccionados a CSV
     */
    public function exportarSeleccionados()
    {
        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debe seleccionar al menos una solicitud para exportar');
            return;
        }

        try {
            $solicitudes = TblSolicitudHe::with(['tipoTrabajo', 'estado', 'fiscalia'])
                ->whereIn('id', $this->seleccionados)
                ->get();

            $filename = 'he_seleccionadas_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ];

            $callback = function() use ($solicitudes) {
                $file = fopen('php://output', 'w');

                // Headers del CSV
                fputcsv($file, [
                    'ID', 'Usuario', 'Fiscalía', 'Fecha', 'Hora Inicial', 'Hora Final',
                    'Minutos Totales', 'Estado', 'Tipo Trabajo', 'Creado'
                ]);

                // Datos
                foreach ($solicitudes as $solicitud) {
                    fputcsv($file, [
                        $solicitud->id,
                        $solicitud->username,
                        $solicitud->fiscalia?->nombre ?? $solicitud->cod_fiscalia,
                        $solicitud->fecha ? $solicitud->fecha->format('d/m/Y') : '',
                        $solicitud->hrs_inicial,
                        $solicitud->hrs_final,
                        $solicitud->total_min,
                        $solicitud->estado?->gls_estado ?? 'Desconocido',
                        $solicitud->tipoTrabajo?->gls_tipo_trabajo ?? 'No especificado',
                        $solicitud->created_at ? $solicitud->created_at->format('d/m/Y H:i') : ''
                    ]);
                }

                fclose($file);
            };

            session()->flash('mensaje', "Exportando {$solicitudes->count()} solicitudes...");
            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar: ' . $e->getMessage());
            Log::error("Error en exportación", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Actualizar automáticamente las estadísticas cada cierto tiempo
     */
    public function actualizarDatos()
    {
        $this->cargarSolicitudes();
        $this->actualizarEstadisticas();
        session()->flash('mensaje', 'Datos actualizados correctamente');
    }

    /**
     * Filtro rápido para mostrar solo las solicitudes con minutos altos
     */
    public function filtrarMinutosAltos($minimo = 480) // 8 horas por defecto
    {
        $this->solicitudes = $this->solicitudes->filter(function($solicitud) use ($minimo) {
            return ($solicitud->total_min ?? 0) >= $minimo;
        });

        session()->flash('mensaje', "Filtrado: {$this->solicitudes->count()} solicitudes con {$minimo}+ minutos");
    }

    /**
     * Verificar estado del sistema antes de operaciones masivas
     */
    public function verificarSistema()
    {
        try {
            // Verificar conexión a BD
            $totalSolicitudes = TblSolicitudHe::count();

            // Verificar estados disponibles
            $estados = TblEstado::whereIn('id', [1, 3, 4])->get();

            // Verificar servicio de flujo
            $flujoService = app(FlujoEstadoService::class);

            $mensaje = "✅ Sistema verificado:\n";
            $mensaje .= "• {$totalSolicitudes} solicitudes en BD\n";
            $mensaje .= "• {$estados->count()} estados disponibles\n";
            $mensaje .= "• Servicio de flujo: Operativo";

            session()->flash('mensaje', $mensaje);

        } catch (\Exception $e) {
            session()->flash('error', 'Error en verificación del sistema: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sistema.aprobaciones-masivas')
            ->layout('components.layouts.app');
    }
}
