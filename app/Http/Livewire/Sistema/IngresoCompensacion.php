<?php

namespace App\Http\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblSolicitudCompensa;

class IngresoCompensacion extends Component
{
    public $fecha_solicitud, $observaciones, $hrs_inicial, $hrs_final;
    public $minutos_solicitados = 0;
    public $saldoDisponible = 0;
    public $puedeCompensar = false;
    public $resumenBolson;

    public $filtro_estado = '';
    public $filtro_fecha = '';
    public $filtro_usuario = '';

    protected $rules = [
        'fecha_solicitud' => 'required|date',
        'observaciones' => 'nullable|string|max:500',
        'hrs_inicial' => 'required',
        'hrs_final' => 'required',
        'minutos_solicitados' => 'required|integer|min:1',
    ];

    public function mount()
    {
        $this->saldoDisponible = auth()->user()->saldo_disponible;
        $this->puedeCompensar = $this->saldoDisponible > 0;
        $this->resumenBolson = $this->obtenerResumenBolson();
    }

    public function obtenerResumenBolson()
    {
        // Lógica para obtener el resumen del bolsón de horas extras
        return [
            'total_pendiente' => 120, // Ejemplo estático, reemplazar con lógica real
            'total_general' => 300, // Ejemplo estático, reemplazar con lógica real
        ];
    }

    public function updatedHrsInicial($value)
    {
        $this->calcularMinutosSolicitados();
    }

    public function updatedHrsFinal($value)
    {
        $this->calcularMinutosSolicitados();
    }

    public function calcularMinutosSolicitados()
    {
        if ($this->hrs_inicial && $this->hrs_final) {
            $inicio = \Carbon\Carbon::createFromFormat('H:i', $this->hrs_inicial);
            $fin = \Carbon\Carbon::createFromFormat('H:i', $this->hrs_final);

            if ($fin->isBefore($inicio)) {
                $fin->addDay(); // Agregar un día si la hora final es antes de la hora inicial
            }

            $this->minutos_solicitados = $inicio->diffInMinutes($fin);
        } else {
            $this->minutos_solicitados = 0;
        }
    }

    public function save()
    {
        $this->validate();

        // Lógica para guardar la solicitud de compensación
        TblSolicitudCompensa::create([
            'fecha_solicitud' => $this->fecha_solicitud,
            'observaciones' => $this->observaciones,
            'hrs_inicial' => $this->hrs_inicial,
            'hrs_final' => $this->hrs_final,
            'minutos_solicitados' => $this->minutos_solicitados,
            'username' => auth()->user()->username,
            'id_estado' => 9, // Estado 9: Pendiente
        ]);

        // Actualizar saldo disponible
        $this->saldoDisponible -= $this->minutos_solicitados;

        session()->flash('mensaje', 'Solicitud de compensación enviada exitosamente.');

        // Reiniciar formulario
        $this->reset(['fecha_solicitud', 'observaciones', 'hrs_inicial', 'hrs_final', 'minutos_solicitados']);
    }

    public function evaluarSolicitud($id)
    {
        $solicitud = TblSolicitudCompensa::findOrFail($id);

        // Lógica para evaluar la solicitud
        // Aquí puedes redirigir a una vista de evaluación o realizar acciones específicas
        session()->flash('info', "Solicitud #{$id} lista para evaluar.");
    }

    public function rechazarSolicitud($id)
    {
        $solicitud = TblSolicitudCompensa::findOrFail($id);

        // Lógica para rechazar la solicitud y devolver los minutos al bolsón
        $solicitud->update(['id_estado' => 11]); // Estado 11: Rechazado
        $this->actualizarBolson($solicitud->minutos_solicitados);

        session()->flash('warning', "Solicitud #{$id} rechazada y minutos devueltos al bolsón.");
    }

    private function actualizarBolson($minutos)
    {
        // Lógica para devolver los minutos al bolsón
        // Implementa la lógica específica según tu modelo y estructura
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtro_estado', 'filtro_fecha', 'filtro_usuario']);
    }

    public function render()
    {
        $query = TblSolicitudCompensa::query();

        // Aplicar filtro por estado
        if ($this->filtro_estado) {
            $query->where('id_estado', $this->filtro_estado);
        }

        // Aplicar filtro por fecha
        if ($this->filtro_fecha) {
            $query->whereDate('fecha_solicitud', $this->filtro_fecha);
        }

        // Aplicar filtro por usuario
        if ($this->filtro_usuario) {
            $query->where('username', 'like', '%' . $this->filtro_usuario . '%');
        }

        $solicitudes = $query->get();

        return view('livewire.sistema.ingreso-compensacion', compact('solicitudes'));
    }
}