<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Services\BolsonService;
use App\Models\TblSolicitudCompensa;
use App\Models\TblEstado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodasCompensaciones extends Component
{
    public $filtro_usuario = '';
    public $filtro_fecha_inicio = '';
    public $filtro_fecha_fin = '';
    public $solicitudes = [];
    public $solicitudSeleccionada = null;
    public $observaciones = '';

    protected $bolsonService;

    public function boot(BolsonService $bolsonService)
    {
        $this->bolsonService = $bolsonService;
    }

    public function mount()
    {
        // Cargar todas las solicitudes al iniciar el componente
        $this->cargarSolicitudes();
    }

    public function cargarSolicitudes()
    {
        // Cargar todas las solicitudes con las relaciones necesarias
        $this->solicitudes = TblSolicitudCompensa::with(['estado', 'persona'])->get();
    }

    public function limpiarFiltros()
    {
        // Restablecer los filtros y recargar todas las solicitudes
        $this->reset(['filtro_usuario', 'filtro_fecha_inicio', 'filtro_fecha_fin']);
        $this->cargarSolicitudes();
    }

    public function aplicarFiltros()
    {
        $query = TblSolicitudCompensa::query();

        // Filtro por usuario
        if (!empty($this->filtro_usuario)) {
            $query->where('username', 'like', '%' . $this->filtro_usuario . '%');
        }

        // Filtro por fecha inicial
        if (!empty($this->filtro_fecha_inicio)) {
            $query->whereDate('fecha_solicitud', '>=', $this->filtro_fecha_inicio);
        }

        // Filtro por fecha final
        if (!empty($this->filtro_fecha_fin)) {
            $query->whereDate('fecha_solicitud', '<=', $this->filtro_fecha_fin);
        }

        // Cargar las solicitudes filtradas
        $this->solicitudes = $query->with(['estado', 'persona'])->get();
    }

     public function rechazarSolicitud($solicitudId)
    {
        // Buscar la solicitud seleccionada
        $this->solicitudSeleccionada = TblSolicitudCompensa::find($solicitudId);

        if (!$this->solicitudSeleccionada) {
            session()->flash('error', 'La solicitud no fue encontrada.');
            return;
        }

        // Cambiar el estado de la solicitud a "Rechazada"
        $this->solicitudSeleccionada->update([
            'id_estado' => 11, // Estado "Rechazada"
            'observaciones' => $this->observaciones,
            'rechazada_por' => Auth::user()->username ?? 'Sistema',
        ]);

        $minutos = (int) ($solicitud->minutos_aprobados ?? $solicitud->minutos ?? 0);
        if ($minutos > 0) {
            $res = $this->bolsonService->crearBolsonDevolución(
                $solicitud->username,
                $minutos,
                'Devolución por rechazo de compensación',
                $solicitud->id
            );

            if (empty($res['success'])) {
                throw new \Exception($res['mensaje'] ?? 'Error al crear bolsón de devolución');
            }
        }

        session()->flash('success', 'La solicitud ha sido rechazada correctamente.');

        // Recargar las solicitudes
        $this->cargarSolicitudes();
    }


    public function render()
    {
        return view('livewire.sistema.todas-compensaciones', [
            'solicitudes' => $this->solicitudes,
        ]);
    }
}
