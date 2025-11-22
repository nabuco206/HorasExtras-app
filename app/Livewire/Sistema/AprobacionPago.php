<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\FlujoEstadoService;

class AprobacionPago extends AprobacionesMasivas
{
    // Marcar origen para la vista si hace falta
    public $origen = 'pago';

    // hacer públicas las propiedades usadas en la vista
    public $solicitudes = [];
    public $seleccionados = [];
    public $filtroEstado = '';
    public $filtroBusqueda = '';
    public $estadisticas = [];

    public function mount(): void
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
     */
    public function cargarSolicitudes()
    {
        $query = $this->baseQuery();

        // filtro específico: solo solicitudes a pago
        $query->where('id_tipo_compensacion', 2);

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

    // Sobrescribe la consulta base para este componente (solo tipo_pago = 2)
    protected function baseQuery()
    {
        return TblSolicitudHe::with(['tipoTrabajo', 'estado'])
            ->where('id_tipo_compensacion', 2)
            ->orderBy('created_at', 'desc');
    }

    protected function statsQuery()
    {
        return TblSolicitudHe::query()->where('id_tipo_compensacion', 2);
    }

    // seleccionar / deseleccionar todas las visibles
    public function seleccionarTodas(): void
    {
        $this->seleccionados = $this->solicitudes->pluck('id')->map(fn($v) => (string)$v)->toArray();
    }

    public function deseleccionarTodas(): void
    {
        $this->seleccionados = [];
    }

    // Acción: aprobar seleccionadas (usa el servicio ya existente)
    public function aprobarSeleccionados(): void
    {
        if (empty($this->seleccionados)) {
            session()->flash('warning', 'No hay solicitudes seleccionadas.');
            return;
        }

        $usuarioId = Auth::id();
        $svc = app(FlujoEstadoService::class);
        $res = $svc->ejecutarTransicionesMultiples($this->seleccionados, null, $usuarioId, 'Aprobación desde UI');

        if (!empty($res['exitoso'])) {
            session()->flash('mensaje', $res['mensaje'] ?? 'Aprobadas correctamente.');
        } else {
            session()->flash('error', $res['mensaje'] ?? 'Error en la aprobación.');
        }

        $this->cargarSolicitudes();
    }

    public function rechazarSeleccionados(): void
    {
        if (empty($this->seleccionados)) {
            session()->flash('warning', 'No hay solicitudes seleccionadas.');
            return;
        }

        $usuarioId = Auth::id();
        $svc = app(FlujoEstadoService::class);
        // suponiendo que en el servicio pasar estadoDestino o un flag para rechazo; si no, adaptar
        $res = $svc->ejecutarTransicionesMultiples($this->seleccionados, /* estadoDestinoRechazo */ 4, $usuarioId, 'Rechazo desde UI');

        if (!empty($res['exitoso'])) {
            session()->flash('mensaje', $res['mensaje'] ?? 'Rechazadas correctamente.');
        } else {
            session()->flash('error', $res['mensaje'] ?? 'Error en el rechazo.');
        }

        $this->cargarSolicitudes();
    }

    // monitor cambios en seleccionados para depuración
    public function updatedSeleccionados()
    {
         $this->selectAll = count($this->seleccionados) === count($this->solicitudes) && count($this->solicitudes) > 0;
    //     \Log::info('AprobacionPago::updatedSeleccionados', ['seleccionados' => $this->seleccionados]);
    //     // opcional: emitir para forzar re-render en la UI
    //     $this->emitSelf('seleccionadosActualizados');
    }

    public function render()
    {
        // recalcular estadísticas si tu componente las usa (opcional)
        $this->estadisticas = [
            'total_solicitudes' => $this->statsQuery()->count(),
            'pendientes' => $this->statsQuery()->where('id_estado', 1)->count(),
            'aprobadas' => $this->statsQuery()->where('id_estado', 3)->count(),
            'rechazadas' => $this->statsQuery()->where('id_estado', 4)->count(),
            'minutos_pendientes_total' => (int)$this->statsQuery()->where('id_estado', 1)->sum('total_min'),
            'minutos_aprobados_total' => (int)$this->statsQuery()->where('id_estado', 3)->sum('total_min'),
            'porcentaje_aprobacion' => 0
        ];

        $totales = $this->estadisticas['aprobadas'] + $this->estadisticas['rechazadas'];
        $this->estadisticas['porcentaje_aprobacion'] = $totales > 0 ? round($this->estadisticas['aprobadas'] * 100 / $totales) : 0;

        return view('livewire.sistema.aprobacion-pago');
    }
}
