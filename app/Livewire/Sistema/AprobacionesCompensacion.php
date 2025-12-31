<?php

namespace App\Livewire\Sistema;

use App\Models\TblSolicitudCompensa;
use App\Models\TblEstado;
use App\Services\CompensacionService;
use App\Services\BolsonService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AprobacionesCompensacion extends Component
{
    use WithPagination;

    protected $listeners = ['aprobarSolicitud', 'rechazarSolicitud', 'rechazarSolicitudConId'];

    public $solicitudesSeleccionadas = [];
    public $filtroEstado = null; // se inicializa en mount por codigo
    public $filtroBusqueda = '';
    public $mostrarModal = false;
    public $solicitudSeleccionada = null;
    public $minutosAprobados = null;
    public $observaciones = '';
    public $accionRealizada = false;
    public $mensajeResultado = '';
    public $estadisticas = null;

    // ids dinámicos de estados (resueltos por codigo)
    public $estadoSolicitadaId = null;
    public $estadoAprobadaId = null;
    public $estadoRechazadaId = null;

    protected $compensacionService;
    protected $bolsonService;

    public function boot(CompensacionService $compensacionService, BolsonService $bolsonService)
    {
        $this->compensacionService = $compensacionService;
        $this->bolsonService = $bolsonService;
    }

    public function mount()
    {
        // resolver ids de estados por codigo para evitar hardcodes
        $this->estadoSolicitadaId = TblEstado::where('codigo', 'COMPENSACION_SOLICITADA')->value('id') ?? 9;
        $this->estadoAprobadaId = TblEstado::where('codigo', 'COMPENSACION_APROBADA_JEFE')->value('id') ?? 10;
        $this->estadoRechazadaId = TblEstado::where('codigo', 'COMPENSACION_RECHAZADA_JEFE')->value('id') ?? 11;

        // establecer filtro por defecto si no hay uno
        if (is_null($this->filtroEstado)) {
            $this->filtroEstado = $this->estadoSolicitadaId;
        }

        $this->actualizarEstadisticas();
    }

    public function render()
    {
        $solicitudes = $this->obtenerSolicitudes();
        return view('livewire.sistema.aprobaciones-compensacion', compact('solicitudes'));
    }

    protected function obtenerSolicitudes()
    {
        $user = Auth::user();

        $query = TblSolicitudCompensa::with(['persona', 'estado'])
            ->orderBy('fecha_solicitud', 'asc');

        // Si el usuario es jefe directo (rol = 2) limitar a su fiscalía (por cod_fiscalia)
        $isJefe = (isset($user->rol) && $user->rol == 2)
               || (isset($user->id_rol) && $user->id_rol == 2)
               || (isset($user->role_id) && $user->role_id == 2);

        if ($isJefe) {
            // intentar varios nombres posibles del campo en User
            $codFiscalia = $user->cod_fiscalia ?? $user->codigo_fiscalia ?? $user->codfiscalia ?? $user->codFiscalia ?? $user->fiscalia_id ?? $user->id_fiscalia ?? null;

            if ($codFiscalia) {
                $query->whereHas('persona', function($q) use ($codFiscalia) {
                    $q->where(function($q2) use ($codFiscalia) {
                        $q2->where('cod_fiscalia', $codFiscalia)
                        //    ->orWhere('codigo_fiscalia', $codFiscalia)
                        //    ->orWhere('fiscalia_id', $codFiscalia)
                        //    ->orWhere('id_fiscalia', $codFiscalia)
                        ;
                    });
                });
            }
        }

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

    public function rechazarSolicitudConId($solicitudId)
    {
        $this->solicitudSeleccionada = TblSolicitudCompensa::with(['persona', 'estado'])->find($solicitudId);
        if (!$this->solicitudSeleccionada) {
            session()->flash('error', 'Solicitud no encontrada.');
            return;
        }
        $this->observaciones = 'Rechazo desde interfaz unificada';
        $this->rechazarSolicitud();
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

    public function rechazarSeleccionadas()
    {
        if (empty($this->solicitudesSeleccionadas)) {
            $this->dispatchBrowserEvent('notify', ['type' => 'error', 'message' => 'No hay solicitudes seleccionadas']);
            return;
        }

        $errores = [];
        foreach ($this->solicitudesSeleccionadas as $id) {
            $sol = TblSolicitudCompensa::find($id);
            if (!$sol) {
                $errores[] = "Solicitud {$id} no encontrada";
                continue;
            }

            if ($sol->id_estado != $this->estadoSolicitadaId) {
                $errores[] = "Solicitud {$id} no está en estado solicitada";
                continue;
            }

            $resultado = $this->compensacionService->procesarRechazoCompensacion(
                $sol,
                auth()->user()->username ?? auth()->user()->name ?? null,
                'Rechazo masivo desde interfaz'
            );

            if (!$resultado['exitoso']) {
                $errores[] = "Solicitud {$id}: {$resultado['mensaje']}";
            }
        }

    $this->solicitudesSeleccionadas = [];
    session()->flash('success', 'Solicitudes rechazadas correctamente');
    // usar evento de navegador en vez de emit para compatibilidad
    $this->dispatchBrowserEvent('refreshList');
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
