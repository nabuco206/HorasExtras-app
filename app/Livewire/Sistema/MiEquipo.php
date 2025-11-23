<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblLider;
use App\Models\TblPersona;
use Illuminate\Support\Facades\Auth;

class MiEquipo extends Component
{
    public $esLider = false;
    public $lider = null;
    public $personas = [];
    public $fiscalia = null;
    
    public function mount()
    {
        $user = Auth::user();
        
        // Verificar si el usuario es líder activo
        $this->lider = TblLider::with(['persona', 'fiscalia'])
            ->whereHas('persona', function($query) use ($user) {
                $query->where('username', $user->username);
            })
            ->where('flag_activo', true)
            ->first();
        
        if ($this->lider) {
            $this->esLider = true;
            $this->fiscalia = $this->lider->fiscalia;
            
            // Obtener todas las personas activas de la misma fiscalía (excepto el líder)
            $this->personas = TblPersona::where('cod_fiscalia', $this->lider->cod_fiscalia)
                ->where('flag_activo', true)
                ->where('id', '!=', $this->lider->persona_id)
                ->orderBy('Nombre')
                ->orderBy('Apellido')
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.sistema.mi-equipo')
            ->layout('components.layouts.app');
    }
}
