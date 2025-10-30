<?php

namespace App\Livewire\Sistema;

use App\Models\TblSolicitudCompensa;
use App\Services\CompensacionService;
use App\Services\BolsonService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AprobacionesCompensacion extends Component
{
    use WithPagination;

    protected $listeners = ['aprobarSolicitud', 'rechazarSolicitud'];

    public $solicitudesSeleccionadas = [];
    public $filtroEstado = 8; // COMPENSACION_SOLICITADA por defecto
    public $filtroBusqueda = '';
    public $mostrarModal = false;
    public $solicitudSeleccionada = null;
    public $minutosAprobados = null;
    public $observaciones = '';
    public $accionRealizada = false;
    public $mensajeResultado = '';
    public $estadisticas = null;

    protected $compensacionService;
    protected $bolsonService;

    public function boot(CompensacionService $compensacionService, BolsonService $bolsonService)
    {
        $this->compensacionService = $compensacionService;
        $this->bolsonService = $bolsonService;
    }

    public function mount()
    {
        $this->actualizarEstadisticas();
    }

    public function render()
    {
        $solicitudes = $this->obtenerSolicitudes();
        return view('livewire.sistema.aprobaciones-compensacion', compact('solicitudes'));
    }

    protected function obtenerSolicitudes()
    {
        $query = TblSolicitudCompensa::with(['persona', 'estado'])
            ->orderBy('fecha_solicitud', 'asc');

        // Filtrar por estado
        if ($this->filtroEstado) {
            $query->where('id_estado', $this->filtroEstado);
        }

        // Filtrar por búsqueda
        if ($this->filtroBusqueda) {
            $query->where(function($q) {
                $q->where('username', 'like', '%' . $this->filtroBusqueda . '%')
                  ->orWhereHas('persona', function($personaQuery) {
                      $personaQuery->where('nombre', 'like', '%' . $this->filtroBusqueda . '%')
                                  ->orWhere('apellido_paterno', 'like', '%' . $this->filtroBusqueda . '%')
                                  ->orWhere('apellido_materno', 'like', '%' . $this->filtroBusqueda . '%');
                  });
            });
        }

        return $query->paginate(10);
    }

    public function seleccionarTodas()
    {
        $solicitudes = $this->obtenerSolicitudes();
        $this->solicitudesSeleccionadas = $solicitudes->pluck('id')->toArray();
    }

    public function deseleccionarTodas()
    {
        $this->solicitudesSeleccionadas = [];
    }

    public function abrirModalAprobacion($solicitudId)
    {
        $this->solicitudSeleccionada = TblSolicitudCompensa::with(['persona', 'estado'])->find($solicitudId);
        $this->minutosAprobados = $this->solicitudSeleccionada->minutos_solicitados;
        $this->observaciones = '';
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->solicitudSeleccionada = null;
        $this->minutosAprobados = null;
        $this->observaciones = '';
    }

    public function aprobarSolicitud()
    {
        if (!$this->solicitudSeleccionada) {
            return;
        }

        // Validar que los minutos aprobados no excedan los solicitados
        if ($this->minutosAprobados > $this->solicitudSeleccionada->minutos_solicitados) {
            session()->flash('error', 'Los minutos aprobados no pueden exceder los solicitados.');
            return;
        }

        $resultado = $this->compensacionService->procesarAprobacionCompensacion(
            $this->solicitudSeleccionada,
            $this->minutosAprobados,
            Auth::user()->username
        );

        if ($resultado['exitoso']) {
            session()->flash('success', $resultado['mensaje']);
            $this->accionRealizada = true;
            $this->mensajeResultado = $resultado['mensaje'];
        } else {
            session()->flash('error', $resultado['mensaje']);
        }

        $this->cerrarModal();
        $this->actualizarEstadisticas();
    }

    public function rechazarSolicitud()
    {
        if (!$this->solicitudSeleccionada) {
            return;
        }

        $resultado = $this->compensacionService->procesarRechazoCompensacion(
            $this->solicitudSeleccionada,
            Auth::user()->username,
            $this->observaciones
        );

        if ($resultado['exitoso']) {
            session()->flash('success', $resultado['mensaje']);
            $this->accionRealizada = true;
            $this->mensajeResultado = $resultado['mensaje'];
        } else {
            session()->flash('error', $resultado['mensaje']);
        }

        $this->cerrarModal();
        $this->actualizarEstadisticas();
    }

    public function aprobarSeleccionadas()
    {
        if (empty($this->solicitudesSeleccionadas)) {
            session()->flash('error', 'Selecciona al menos una compensación para aprobar.');
            return;
        }

        $resultado = $this->compensacionService->procesarAprobacionesMultiples(
            $this->solicitudesSeleccionadas,
            Auth::user()->username
        );

        if ($resultado['exitoso']) {
            session()->flash('success', $resultado['mensaje']);
            $this->solicitudesSeleccionadas = [];
        } else {
            session()->flash('error', $resultado['mensaje']);
        }

        $this->actualizarEstadisticas();
    }

    public function actualizarEstadisticas()
    {
        $this->estadisticas = $this->compensacionService->obtenerEstadisticas();
    }

    public function obtenerSaldoBolson($username)
    {
        try {
            return $this->bolsonService->obtenerSaldoDisponible($username);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function validarSolicitud($solicitudId)
    {
        $solicitud = TblSolicitudCompensa::find($solicitudId);
        if (!$solicitud) {
            return ['valida' => false, 'mensaje' => 'Solicitud no encontrada'];
        }

        return $this->compensacionService->validarSolicitudParaAprobacion($solicitud);
    }

    public function updated($property)
    {
        if (in_array($property, ['filtroEstado', 'filtroBusqueda'])) {
            $this->resetPage();
            $this->deseleccionarTodas();
        }
    }
}
