<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblPersona;
use App\Models\TblBolsonTiempo;
use App\Models\TblFiscalia;
use App\Models\TblTipoCompensacion;
use Illuminate\Support\Facades\Auth;

class MonitoreoTiempo extends Component
{
    public $fiscaliaSeleccionada = '';
    public $tipoCompensacionSeleccionada = '';
    public $personas = [];
    public $fiscalias = [];
    public $tiposCompensacion = [];
    public $tieneAcceso = false;

    public function mount()
    {
        $user = Auth::user();

        // Verificar si el usuario tiene rol UDP (3), JUDP (4) o DER (5)
        $rolesPermitidos = [3, 4, 5]; // UDP, JUDP, DER
        $this->tieneAcceso = in_array($user->id_rol, $rolesPermitidos);

        if (!$this->tieneAcceso) {
            return;
        }

        // Obtener todas las fiscalías
        $this->fiscalias = TblFiscalia::orderBy('gls_fiscalia')->get();

        // Obtener tipos de compensación
        $this->tiposCompensacion = TblTipoCompensacion::all();

        // Cargar datos iniciales (todas las fiscalías, todos los tipos)
        $this->cargarPersonas();
    }

    public function updatedFiscaliaSeleccionada()
    {
        $this->cargarPersonas();
    }

    public function updatedTipoCompensacionSeleccionada()
    {
        $this->cargarPersonas();
    }

    private function cargarPersonas()
    {
        if (!$this->tieneAcceso) {
            return;
        }

        $query = TblPersona::with('escalafon', 'fiscalia')
            ->where('flag_activo', true)
            ->where('id_rol', 1);

        // Filtrar por fiscalía si se seleccionó una
        if (!empty($this->fiscaliaSeleccionada)) {
            $query->where('cod_fiscalia', $this->fiscaliaSeleccionada);
        }

        $personas = $query->orderBy('Nombre')->orderBy('Apellido')->get();

        // Calcular tiempo disponible para cada persona
        foreach ($personas as $persona) {
            $queryBolsones = TblBolsonTiempo::vigentes()->where('username', $persona->username)
                ->whereHas('solicitudHe', function($q) {
                    $q->whereIn('id_tipo_compensacion', [1, 2])
                      ->where('id_estado', 4);
                    if (!empty($this->tipoCompensacionSeleccionada)) {
                        $q->where('id_tipo_compensacion', $this->tipoCompensacionSeleccionada);
                    }
                });

            $saldo = $queryBolsones->sum('saldo_min');
            $persona->tiempo_disponible = (int) $saldo;
        }

        // Ordenar por tiempo disponible descendente
        $this->personas = $personas->sortByDesc('tiempo_disponible');
    }

    public function render()
    {
        return view('livewire.sistema.monitoreo-tiempo')
            ->layout('components.layouts.app');
    }
}
