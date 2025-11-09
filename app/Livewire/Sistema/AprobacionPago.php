<?php

namespace App\Livewire\Sistema;

use App\Livewire\Sistema\AprobacionesMasivas;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Log;

class AprobacionPago extends AprobacionesMasivas
{
    // Marcar origen para la vista si hace falta
    public $origen = 'pago';

    public function mount()
    {
        // Llamar al mount del padre para inicializar tipos/estados, etc.
        if (method_exists(get_parent_class($this), 'mount')) {
            parent::mount();
        }

        // Forzar filtro visual si lo usas en la UI
        $this->filtroEstado = $this->filtroEstado ?? 1;
        $this->actualizarEstadisticas();
        $this->cargarSolicitudes();
    }

    /**
     * Cargar solo solicitudes marcadas como 'pago'.
     * Ahora filtra por id_tipo_compensacion = 1
     */
    public function cargarSolicitudes()
    {
        $query = $this->baseQuery();

        // filtro específico: solo solicitudes a pago
        $query->where('id_tipo_compensacion', 1);

        // Filtro por estado (heredado del padre)
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

    /**
     * Estadísticas solo para solicitudes a pago.
     */
    public function actualizarEstadisticas()
    {
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

    protected function baseQuery()
    {
        return TblSolicitudHe::with(['tipoTrabajo', 'estado'])
            ->where('id_tipo_compensacion', 1)
            ->orderBy('created_at', 'desc');
    }

    protected function statsQuery()
    {
        return TblSolicitudHe::query()->where('id_tipo_compensacion', 1);
    }

    public function render()
    {
        return view('livewire.sistema.aprobacion-pago')
            ->layout('components.layouts.app');
    }

    // Sobrescribir para aprobar seleccionados siguiendo el flujo en DB
    public function aprobarSeleccionados()
    {
        // Log previo
        Log::info('AprobacionPago::aprobarSeleccionados invocado', [
            'seleccionados' => $this->seleccionados,
            'auth_id' => auth()->id(),
            'auth_user' => auth()->user()?->toArray()
        ]);

        if (empty($this->seleccionados)) {
            session()->flash('warning', 'No hay solicitudes seleccionadas.');
            return;
        }

        // Asegurar que pasamos un array de IDs simples al servicio
        $solicitudesIds = array_values($this->seleccionados);
        $usuarioId = auth()->user()?->id ?? auth()->id();
        $flujoService = app(\App\Services\FlujoEstadoService::class);

        // Llamada explícita al servicio (estadoDestino = null para que el servicio decida)
        $resultado = $flujoService->ejecutarTransicionesMultiples($solicitudesIds, null, $usuarioId, 'Aprobación desde AprobacionPago');

        // Log posterior para verificar lo que devolvió el servicio
        Log::info('AprobacionPago::resultado ejecucion servicio', [
            'solicitudes_enviadas' => $solicitudesIds,
            'resultado' => $resultado
        ]);

        $this->ultimaOperacion = $resultado;
        $this->mostrarResultados = true;

        $this->actualizarEstadisticas();
        $this->cargarSolicitudes();

        if (!empty($resultado['exitoso'])) {
            session()->flash('mensaje', $resultado['mensaje'] ?? 'Operación completada');
        } else {
            session()->flash('error', $resultado['mensaje'] ?? 'Error en la operación');
        }

        Log::info('Aprobación masiva desde AprobacionPago', [
            'seleccionados' => $this->seleccionados,
            'resultado' => $resultado
        ]);
    }

    // Sobrescribir para deshabilitar rechazo masivo en esta vista
    public function rechazarSeleccionados()
    {
        Log::info('Intento de rechazar en AprobacionPago deshabilitado', [
            'seleccionados' => $this->seleccionados
        ]);

        session()->flash('warning', 'Acción deshabilitada: no se pueden rechazar solicitudes desde Aprobación Pago por ahora.');
        $this->cargarSolicitudes();
    }

    // Sobrescribir exportación para deshabilitarla en esta vista (si hay botón)
    public function exportarSeleccionados()
    {
        Log::info('Intento de exportar en AprobacionPago deshabilitado', [
            'seleccionados' => $this->seleccionados
        ]);

        session()->flash('warning', 'Exportación deshabilitada en Aprobación Pago por ahora.');
    }

    // Sobrescribir método de prueba si existe en padre
    public function probarAprobacion()
    {
        Log::info('Intento de prueba en AprobacionPago deshabilitado');
        session()->flash('warning', 'Pruebas deshabilitadas en Aprobación Pago por ahora.');
    }
}
