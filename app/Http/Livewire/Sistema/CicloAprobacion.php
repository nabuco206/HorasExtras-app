<?php
namespace App\Http\Livewire\Sistema;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CicloAprobacion extends Component {
    use WithPagination;

    public $search = '';
    public $id_estado = 0; // Cambia este valor según el estado que quieras filtrar
    public $selectedIds = [];
    public $modalEstadosVisible = false;
    public $estadosSolicitud = [];
    public $tipos_trabajo = [];
    public $estados = [];

    public function mount()
    {
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
    }

    public function render()
    {
        $user = Auth::user();
        $cod_fiscalia = $user->cod_fiscalia;

        $solicitudes = TblSolicitudHe::where('id_estado', $this->id_estado)
            ->where('cod_fiscalia', $cod_fiscalia)
            ->where(function($query) {
                $query->where('id', 'like', "%{$this->search}%")
                      ->orWhere('descripcion', 'like', "%{$this->search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.sistema.ciclo-aprobacion', [
            'solicitudes' => $solicitudes,
            'tipos_trabajo' => $this->tipos_trabajo,
            'estados' => $this->estados,
            'modalEstadosVisible' => $this->modalEstadosVisible,
            'estadosSolicitud' => $this->estadosSolicitud,
        ]);
    }

    public function cambiarEstadoSeleccionados($nuevoEstado)
    {
        // Aquí va la lógica para actualizar el estado y loguear en tbl_seguimiento
        // Ejemplo:
        $user = Auth::user();
        foreach ($this->selectedIds as $id) {
            $solicitud = TblSolicitudHe::find($id);
            if ($solicitud) {
                $solicitud->id_estado = $nuevoEstado;
                $solicitud->save();
                // Log en tbl_seguimiento
                \App\Models\TblSeguimientoSolicitud::create([
                    'id_solicitud_he' => $id,
                    'id_estado' => $nuevoEstado,
                    'username' => $user->name,
                ]);
            }
        }
        $this->selectedIds = [];
        session()->flash('message', 'Solicitudes actualizadas correctamente.');
    }


    public function verEstados($idSolicitud)
    {
    \Log::info('Llamada a verEstados SOLO LOG', ['id' => $idSolicitud]);
    }
}
