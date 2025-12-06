<?php

namespace App\Livewire\Sistema;

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

    public function mount()
    {
        $user = Auth::user();

        // detectar si el usuario es jefe directo (rol = 2)
        $isJefe = (isset($user->rol) && $user->rol == 2)
               || (isset($user->id_rol) && $user->id_rol == 2)
               || (isset($user->role_id) && $user->role_id == 2);

        // intentar varios nombres posibles del campo cod_fiscalia en User
        $userCodFiscalia = $user->cod_fiscalia ?? $user->codigo_fiscalia ?? $user->codfiscalia ?? $user->codFiscalia ?? $user->fiscalia_id ?? $user->id_fiscalia ?? null;

        if ($isJefe && $userCodFiscalia) {
            // vista para jefes: mostrar personas de la fiscalía del usuario
            $this->esLider = true;
            $this->fiscalia = ['cod_fiscalia' => $userCodFiscalia];

            $this->personas = TblPersona::with('escalafon')
                ->where('cod_fiscalia', $userCodFiscalia)
                ->where('flag_activo', true)
                ->orderBy('Nombre')
                ->orderBy('Apellido')
                ->get();

            foreach ($this->personas as $p) {
                $saldo = TblBolsonTiempo::vigentes()->where('username', $p->username)
                    ->whereHas('solicitudHe', function($q) {
                        $q->whereIn('id_tipo_compensacion', [1, 2])
                          ->where('id_estado', 4);
                    })
                    ->sum('saldo_min');
                $p->tiempo_disponible = (int) $saldo;
            }

            $this->personas = $this->personas->sortByDesc('tiempo_disponible');

            $this->escalafon = TblPersona::select('id', 'Nombre', 'Apellido', 'Escalafon')
                ->where('cod_fiscalia', $userCodFiscalia)
                ->where('flag_activo', true)
                ->orderBy('Escalafon')
                ->get();

            return;
        }

        // Si no es jefe directo, usar la lógica de líder existente
        $this->lider = TblLider::with(['persona', 'fiscalia'])
            ->whereHas('persona', function($query) use ($user) {
                $query->where('username', $user->username);
            })
            ->where('flag_activo', true)
            ->first();

        if ($this->lider) {
            $this->esLider = true;
            $this->fiscalia = $this->lider->fiscalia;

            $this->personas = TblPersona::with('escalafon')
                ->where('cod_fiscalia', $this->lider->cod_fiscalia)
                ->where('flag_activo', true)
                ->where('id', '!=', $this->lider->persona_id)
                ->orderBy('Nombre')
                ->orderBy('Apellido')
                ->get();

            foreach ($this->personas as $p) {
                $saldo = TblBolsonTiempo::vigentes()->where('username', $p->username)
                    ->whereHas('solicitudHe', function($q) {
                        $q->whereIn('id_tipo_compensacion', [1, 2])
                          ->where('id_estado', 4);
                    })
                    ->sum('saldo_min');
                $p->tiempo_disponible = (int) $saldo;
            }

            $this->personas = $this->personas->sortByDesc('tiempo_disponible');

            $this->escalafon = TblPersona::select('id', 'Nombre', 'Apellido', 'Escalafon')
                ->where('cod_fiscalia', $this->lider->cod_fiscalia)
                ->where('flag_activo', true)
                ->orderBy('Escalafon')
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.sistema.mi-equipo')
            ->layout('components.layouts.app');
    }
}
