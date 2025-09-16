<?php

use App\Models\User;
use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public bool $modalConfirmarCambioEstado = false;
    public string $accionEstado = '';
    public $selectedSolicitudes = [];
    public bool $selectAll = false;
    public string $username = '';
    public int $id_tipo_trabajo = 0;
    public string $fecha = '';
    public string $hrs_inicial = '';
    public string $hrs_final = '';
    public bool $propone_pago = false;
    public $tipos_trabajo = [];
    public $estados = [];
    public $solicitudes = [];
    public $modalEstadosVisible = false;
    public $estadosSolicitud = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->username = Auth::user()->name;
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
        // Filtrar por cod_fiscalia del usuario y estado (ejemplo: id_estado = 1)
        $codFiscalia = Auth::user()->cod_fiscalia ?? null;
        $estadoFiltrado = 0; // Cambia este valor según el estado que quieras filtrar
        $this->solicitudes = \App\Models\TblSolicitudHe::where('cod_fiscalia', $codFiscalia)
            ->where('id_estado', $estadoFiltrado)
            ->orderByDesc('id')
            ->get();
    $this->selectedSolicitudes = [];
    $this->selectAll = false;
    }

    // Abre el modal y setea la acción (aprobar/rechazar)
    public function abrirModalCambioEstado($accion)
    {
        $this->accionEstado = $accion;
        $this->modalConfirmarCambioEstado = true;
    }

    // Confirma el cambio de estado y realiza el update y log
    public function confirmarCambioEstado()
    {
        $nuevoEstado = $this->accionEstado === 'aprobar' ? 2 : 3; // 2=aprobado, 3=rechazado
        if (!empty($this->selectedSolicitudes)) {
            \App\Models\TblSolicitudHe::whereIn('id', $this->selectedSolicitudes)
                ->update(['id_estado' => $nuevoEstado]);
            // Log en tbl_seguimiento
            foreach ($this->selectedSolicitudes as $idSolicitud) {
                \App\Models\TblSeguimientoSolicitud::create([
                    'id_solicitud_he' => $idSolicitud,
                    'id_estado' => $nuevoEstado,
                    'created_at' => now(),
                ]);
            }
        }
        $this->modalConfirmarCambioEstado = false;
        $this->selectedSolicitudes = [];
        // Refrescar listado
        $codFiscalia = Auth::user()->cod_fiscalia ?? null;
        $estadoFiltrado = 0;
        $this->solicitudes = \App\Models\TblSolicitudHe::where('cod_fiscalia', $codFiscalia)
            ->where('id_estado', $estadoFiltrado)
            ->orderByDesc('id')
            ->get();
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedSolicitudes) === $this->solicitudes->count()) {
            $this->selectedSolicitudes = [];
        } else {
            $this->selectedSolicitudes = $this->solicitudes->pluck('id')->toArray();
        }
    }

    public function updatedSelectedSolicitudes()
    {
    $this->selectAll = count($this->selectedSolicitudes) === $this->solicitudes->count();
    }
    // $this->propone_pago = false;

    /**
     * Update the profile information for the currently authenticated user.
     */

    public function verEstados($idSolicitud): void
    {
        $seguimientos = \App\Models\TblSeguimientoSolicitud::where('id_solicitud_he', $idSolicitud)
            ->with('estado')
            ->orderBy('created_at')
            ->get();

        $estados = [];

        foreach ($seguimientos as $seguimiento) {
            $estados[] = [
                'idSolicitud' => $idSolicitud,
                'id' => $seguimiento->id,
                'gls_estado' => $seguimiento->estado->gls_estado ,
                'created_at' => $seguimiento->created_at->format('d/m/Y H:i'),
            ];
        }

        $this->estadosSolicitud = $estados;
        $this->modalEstadosVisible = true;
    }


};

