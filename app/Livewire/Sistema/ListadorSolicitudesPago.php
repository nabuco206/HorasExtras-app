<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\TblSolicitudCompensa;
use App\Models\TblSolicitudHe;
use App\Models\TblEstado;

class ListadorSolicitudesPago extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $estadoId = null;
    public $selected = null; // solicitud seleccionada para detalle

    protected $listeners = ['refreshList' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        // Para este listador usamos la tabla de HE y filtramos por id_tipo_compensacion = 2
        $query = TblSolicitudHe::query()->where('id_tipo_compensacion', 2);

        // JD ve solo su cod_fiscalia
        if ($user && intval($user->role_id) === 2) {
            $cod = $user->cod_fiscalia ?? null;
            if ($cod) $query->where('cod_fiscalia', $cod);
        }

        // filtro por estado si se seleccionó (opcional)
        if ($this->estadoId) {
            $query->where('id_estado', $this->estadoId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%'.$this->search.'%')
                  ->orWhere('observaciones', 'like', '%'.$this->search.'%');
            });
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $estados = TblEstado::orderBy('descripcion')->get();

        return view('livewire.sistema.listador-solicitudes-pago', compact('solicitudes','estados'));
    }

    public function mostrarDetalle($id)
    {
        $this->selected = TblSolicitudCompensa::find($id);
        $this->dispatchBrowserEvent('show-solicitud-modal');
    }
}
