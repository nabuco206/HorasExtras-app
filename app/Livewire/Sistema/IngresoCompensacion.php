<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblSolicitudCompensa;

class IngresoCompensacion extends Component
{
    public $username;
    public $cod_fiscalia;
    public $fecha;
    public $hrs_inicial;
    public $hrs_final;
    public $total_min;

    public function mount()
    {
        $this->username = auth()->user()->name;
        $this->cod_fiscalia = auth()->user()->cod_fiscalia;
    }

    public function save()
    {
        $this->validate([
            'username' => 'required|string',
            'cod_fiscalia' => 'required|integer',
            'fecha' => 'required|date',
            'hrs_inicial' => 'required',
            'hrs_final' => 'required',
            'total_min' => 'nullable|integer',
        ]);

        TblSolicitudCompensa::create([
            'username' => $this->username,
            'cod_fiscalia' => $this->cod_fiscalia,
            'fecha' => $this->fecha,
            'hrs_inicial' => $this->hrs_inicial,
            'hrs_final' => $this->hrs_final,
            'total_min' => $this->total_min,
        ]);

        session()->flash('mensaje', 'Solicitud ingresada correctamente');
        $this->reset(['fecha', 'hrs_inicial', 'hrs_final', 'total_min']);
    }

    public function render()
    {
        return view('livewire.sistema.ingreso-compensacion');
    }
}
