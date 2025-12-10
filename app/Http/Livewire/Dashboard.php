<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\TblSolicitudHe;
use App\Models\TblSolicitudCompensa;

class Dashboard extends Component
{
    public $totalSolicitudesPendientes = 0;

    public function mount()
    {
        $user = Auth::user();
        
        // Verificar si el usuario es JD (id_rol = 2)
        if ($user->id_rol == 2) {
            $codFiscalia = $user->cod_fiscalia;

            // Contar solicitudes pendientes en tbl_solicitud_hes
            $solicitudesHesPendientes = TblSolicitudHe::where('cod_fiscalia', $codFiscalia)
                ->where('id_estado', 9) // Estado "Pendiente"
                ->count();

            // Contar solicitudes pendientes en tbl_solicitud_compensas
            $solicitudesCompensasPendientes = TblSolicitudCompensa::where('cod_fiscalia', $codFiscalia)
                ->where('id_estado', 9) // Estado "Pendiente"
                ->count();

            // Total de solicitudes pendientes
            $this->totalSolicitudesPendientes = $solicitudesHesPendientes + $solicitudesCompensasPendientes;
        }
    }

    public function render()
    {
        
        return view('livewire.dashboard', [
            'totalSolicitudesPendientes' => $this->totalSolicitudesPendientes,
        ]);
    }
}
