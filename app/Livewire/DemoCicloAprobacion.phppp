<?php

namespace App\Livewire;

use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use App\Models\TblSeguimientoSolicitud;
use Livewire\Component;

class DemoCicloAprobacion extends Component
{
    public $selectAll = false;
    public $seleccionados = [];
    public $solicitudes = [];
    public $tipos_trabajo = [];
    public $estados = [];
    public $modalEstadosVisible = false;
     public $estadosSolicitud = [];

    public function mount()
    {
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
        $codFiscalia = auth()->user()->cod_fiscalia;
        $this->solicitudes = \App\Models\TblSolicitudHe::where('cod_fiscalia', $codFiscalia)
                ->orderByDesc('id')
                ->get();

    }

    // public function updatedSelectAll($value)
    // {
    //     if ($value) {
    //         $this->seleccionados = collect($this->solicitudes)->pluck('id')->toArray();
    //     } else {
    //         $this->seleccionados = [];
    //     }
    // }

    public function updatedSeleccionados()
    {
        $this->selectAll = count($this->seleccionados) === count($this->solicitudes);
    }

    private function cambiarEstadoSeleccionados(int $nuevoEstado, string $mensaje)
    {
        \App\Models\TblSolicitudHe::whereIn('id', $this->seleccionados)
            ->update(['id_estado' => $nuevoEstado]);

        foreach ($this->seleccionados as $id) {
            TblSeguimientoSolicitud::create([
                'id_solicitud_he' => $id,
                'username' => auth()->user()->username,
                'id_estado' => $nuevoEstado,
            ]);
        }

        session()->flash('mensaje', $mensaje . implode(', ', $this->seleccionados));
        $this->reset('seleccionados', 'selectAll');
        $this->solicitudes = \App\Models\TblSolicitudHe::orderByDesc('id')->get();
    }

    public function aprobarSeleccionados()
    {
        $this->cambiarEstadoSeleccionados(4, 'IDs Boton Apruebo: ');
    }

    public function rechazarSeleccionados()
    {
        $this->cambiarEstadoSeleccionados(3, 'IDs Rechazados: ');
    }

    public function verEstados($idSolicitud)
    {
        \Log::info('LPUA:');
        $service = app(\App\Services\SolicitudHeService::class);
        $this->estadosSolicitud = $service->obtenerEstados($idSolicitud);
        \Log::info($idSolicitud);
        $this->modalEstadosVisible = true;

    }

    public function render()
    {
        return view('livewire.demo-ciclo-aprobacion');
    }
}
