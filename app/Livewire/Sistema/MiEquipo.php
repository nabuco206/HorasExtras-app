<?php

namespace App\Livewire\Sistema;
use Illuminate\Support\Facades\Log;

use Livewire\Component;
use App\Models\TblLider;
use App\Models\TblPersona;
use App\Models\TblBolsonTiempo;
use Illuminate\Support\Facades\Auth;

class MiEquipo extends Component
{
    public $esLider = false;
    public $lider = null;
    public $personas = [];
    public $fiscalia = null;
    public $escalafon = [];
    public $gls_fiscalia = null;

    public function mount()
    {
        $user = Auth::user();
        $isJefeLider = $user->flag_lider ?? false;
        $this->gls_fiscalia =  $user->fiscalia->gls_fiscalia ;
       
        // detectar si el usuario es jefe directo (rol = 2)
        $isJefe = (isset($user->id_rol) && in_array($user->id_rol, [2, 4, 5]));
        // log::info('isJefe: ' . $user->username . ', esFiscalíaLider: ' . ($isJefe ? 'SI' : 'NO'));
        
        // intentar varios nombres posibles del campo cod_fiscalia en User
        $userCodFiscalia = $user->cod_fiscalia ??  null;
       
        if (($isJefe || $isJefeLider) && $userCodFiscalia) {
            // vista para jefes: mostrar personas de 
         
            $this->esLider = true;
            $this->fiscalia = ['cod_fiscalia' => $userCodFiscalia];

            $this->personas =  TblPersona::select('id', 'nombre', 'apellido','username','id_escalafon', 'flag_activo')
                ->with('escalafon')
                ->where('cod_fiscalia', $userCodFiscalia)
                ->wherein('id_rol', [1, 3])
                ->where('flag_activo', true)
                ->orderBy('Nombre')
                ->orderBy('Apellido')
                ->get();
            

            foreach ($this->personas as $p) {
                $saldo = TblBolsonTiempo::vigentes()->where('username', $p->username)->sum('saldo_min');
                $p->tiempo_disponible = (int) $saldo;
            }

            $this->personas = $this->personas->sortByDesc('tiempo_disponible');

            $this->escalafon = TblPersona::select('id', 'Nombre', 'Apellido')
                ->where('cod_fiscalia', $userCodFiscalia)
                ->where('flag_activo', true)
                // ->orderBy('Escalafon')
                ->get();

            return;
        }

        // Si no es jefe directo, usar la lógica de líder existente
   
        
    }

    public function render()
    {
        return view('livewire.sistema.mi-equipo')
            ->layout('components.layouts.app');
    }
}
