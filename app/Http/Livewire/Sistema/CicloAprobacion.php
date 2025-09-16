<?php

namespace App\Http\Livewire\Sistema;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\SolicitudHeService;
use Illuminate\Support\Facades\Auth;

class CicloAprobacion extends Component
{
    use WithPagination;

    public $search = '';
    public $id_estado = 1; // Cambia este valor según el estado que quieras filtrar

    public function render()
    {
        $user = Auth::user();
        $cod_fiscalia = $user->cod_fiscalia;

        $filtros = [
            'id_estado' => $this->id_estado,
            'cod_fiscalia' => $cod_fiscalia,
        ];

        $servicio = app(SolicitudHeService::class);
        $solicitudes = $servicio->getSolicitudesFiltradas($filtros, 10);

        // Filtro adicional de búsqueda (id o descripción)
        if ($this->search) {
            $solicitudes = $solicitudes->filter(function($item) {
                return str_contains($item->id, $this->search) ||
                       str_contains(strtolower($item->descripcion ?? ''), strtolower($this->search));
            });
        }

        return view('livewire.sistema.ciclo-aprobacion', [
            'solicitudes' => $solicitudes
        ]);
    }
}
