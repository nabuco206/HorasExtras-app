<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblSolicitudHe;
use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\FlujoEstadoService;

class AprobacionesUnificadas extends Component
{
    public $tipo_compensacion = 1;
    public $rol = null;

    // propiedades que la vista espera
    public $tipos_trabajo = [];
    public $estados = [];
    public $solicitudes = [];
    public $seleccionados = [];
    public $selectAll = false;
    public $mostrarSoloPendientes = true;
    public $filtroBusqueda = '';
    public $filtroEstado = 1; // pendiente por defecto
    public $estadisticas = [];
    public $ultimaOperacion = null;
    public $mostrarResultados = false;

    // nuevo: título dinámico para el H1
    public $titulo = '🚀 Aprobaciones Masivas';

    public $modalEstadosVisible = false;
    public $estadosSolicitud = [];

    public function mount($tipo = 1, $rol = null, $titulo = null, $estado = null)
    {
        $this->tipo_compensacion = (int) $tipo;
        $this->rol = $rol;

        // cargar catálogos usados por la vista
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();

        // permitir pasar estado por query (?estado=...)
        $this->filtroEstado = $estado ?? request()->query('estado', $this->filtroEstado);

        // título: prioridad -> parametro $titulo pasado desde wrapper / query param ?titulo / generado por defecto
        $this->titulo = $titulo ?? request()->query('titulo', null) ?? $this->generarTitulo();

        $this->actualizarEstadisticas();
        $this->cargarSolicitudes();
    }

    // genera un título por defecto según tipo y estado
    protected function generarTitulo(): string
    {
        $tipoText = $this->tipo_compensacion === 2 ? 'Pago' : 'HE';

        // Buscar el estado dinámicamente en la lista de estados cargados
        $estado = $this->estados->firstWhere('id', $this->filtroEstado);

        // Personalizar títulos según el estado
        if ($estado) {
            switch ($estado->id) {
                case 1:
                    return "🚀 Solicitudes Ingresadas de {$tipoText}";
                case 3:
                    return "✅ Aprobaciones Completadas de {$tipoText}";
                case 4:
                    return "❌ Rechazos de {$tipoText}";
                default:
                    return "🚀 Aprobaciones Masivas de {$tipoText} — {$estado->descripcion}";
            }
        }

        return "🚀 Aprobaciones Masivas de {$tipoText} — Todas";
    }

    protected function baseQuery()
    {
        return TblSolicitudHe::with(['tipoTrabajo', 'estado'])
            ->where('id_tipo_compensacion', $this->tipo_compensacion)
            ->orderBy('created_at', 'desc');
    }

    protected function statsQuery()
    {
        return TblSolicitudHe::query()->where('id_tipo_compensacion', $this->tipo_compensacion);
    }

    public function cargarSolicitudes(): void
    {
        $query = $this->baseQuery();

        if (!empty($this->filtroEstado)) {
            $query->where('id_estado', $this->filtroEstado);
        }

        if (!empty($this->filtroBusqueda)) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->filtroBusqueda . '%')
                  ->orWhere('id', 'like', '%' . $this->filtroBusqueda . '%');
            });
        }

        $this->solicitudes = $query->orderByDesc('id')->get();
        $this->reset(['seleccionados', 'selectAll']);

        // actualizar bandera para la vista
        $this->mostrarResultados = $this->solicitudes->isNotEmpty();

        Log::info('AprobacionesUnificadas::cargarSolicitudes', [
            'tipo' => $this->tipo_compensacion,
            'estado_filtro' => $this->filtroEstado,
            'ids' => $this->solicitudes->pluck('id')->all()
        ]);
    }

    public function seleccionarTodas(): void
    {
        $this->seleccionados = $this->solicitudes->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->selectAll = true;
    }

    public function deseleccionarTodas(): void
    {
        $this->seleccionados = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->seleccionados = $this->solicitudes->pluck('id')->map(fn($v) => (string)$v)->toArray();
        } else {
            $this->seleccionados = [];
        }
    }

    public function updatedSeleccionados()
    {
        $this->selectAll = count($this->seleccionados) === count($this->solicitudes) && count($this->solicitudes) > 0;
        Log::info('AprobacionesUnificadas::updatedSeleccionados', ['seleccionados' => $this->seleccionados]);
    }

    public function aprobarSeleccionados(): void
    {
        if (empty($this->seleccionados)) {
            session()->flash('warning', 'No hay solicitudes seleccionadas.');
            return;
        }

        $usuarioId = Auth::id();
        $svc = app(FlujoEstadoService::class);
        $resultado = $svc->ejecutarTransicionesMultiples($this->seleccionados, null, $usuarioId, 'Aprobación desde UI');

        $this->ultimaOperacion = $resultado;
        $this->mostrarResultados = true;

        session()->flash($resultado['exitoso'] ? 'mensaje' : 'error', $resultado['mensaje'] ?? ($resultado['exitoso'] ? 'Aprobadas.' : 'Error en aprobación.'));
        $this->actualizarEstadisticas();
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

        $resultado = $svc->ejecutarTransicionesMultiples($this->seleccionados, 4, $usuarioId, 'Rechazo desde UI');

        $this->ultimaOperacion = $resultado;
        $this->mostrarResultados = true;

        session()->flash($resultado['exitoso'] ? 'mensaje' : 'error', $resultado['mensaje'] ?? ($resultado['exitoso'] ? 'Rechazadas.' : 'Error en rechazo.'));
        $this->actualizarEstadisticas();
        $this->cargarSolicitudes();
    }

    public function cerrarResultados()
    {
        $this->mostrarResultados = false;
        $this->ultimaOperacion = null;
    }

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

    public function verEstados($idSolicitud)
    {
        $service = app(\App\Services\SolicitudHeService::class);
        $this->estadosSolicitud = $service->obtenerEstados($idSolicitud);
        $this->modalEstadosVisible = true;
    }

    public function render()
    {
        return view('livewire.sistema.aprobaciones-masivas')
            ->layout('components.layouts.app');
    }
}
