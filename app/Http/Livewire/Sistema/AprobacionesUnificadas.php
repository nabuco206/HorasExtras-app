<?php

namespace App\Http\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblSolicitudCompensa;

class AprobacionesUnificadas extends Component
{
    public $tipo;
    public $rol;
    public $estado;
    public $titulo;

    public function mount($tipo, $rol, $estado, $titulo)
    {
        $this->tipo = $tipo;
        $this->rol = $rol;
        $this->estado = $estado;
        $this->titulo = $titulo;
    }

    public function render()
    {
        $user = auth()->user();
        $query = TblSolicitudCompensa::query();

        // Filtrar por rol
        if ($user->id_rol == 2) { // JD
            $query->where('cod_fiscalia', $user->cod_fiscalia);
        } elseif ($user->id_rol == 3) { // UDP
            // UDP puede ver todas las fiscalías, no se aplica filtro adicional
        } else {
            abort(403, 'No tiene permiso para acceder a esta página.');
        }

        // Filtrar por estado si está definido
        if ($this->estado) {
            $query->where('id_estado', $this->estado);
        }

        // Filtrar por tipo si está definido
        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        $solicitudes = $query->get();

        return view('livewire.sistema.aprobaciones-unificadas', compact('solicitudes'));
    }
}
