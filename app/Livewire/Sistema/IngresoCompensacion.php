<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblSolicitudCompensa;
use App\Services\BolsonService;
use App\Services\CompensacionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IngresoCompensacion extends Component
{
    public $username;
    public $cod_fiscalia;
    public $fecha_solicitud;
    public $hrs_inicial;
    public $hrs_final;
    public $minutos_solicitados;
    public $observaciones;
    public $solicitudes = [];

    // Para mostrar información del bolsón
    public $saldoDisponible = 0;
    public $resumenBolson = null;
    public $puedeCompensar = false;

    protected $compensacionService;

    public function boot(CompensacionService $compensacionService)
    {
        $this->compensacionService = $compensacionService;
    }

    public function mount()
    {
        $user = Auth::user();
        $this->username = $user->name ?? 'test_user';
        $this->cod_fiscalia = $user->cod_fiscalia ?? 1;

        $this->cargarDatos();
        $this->actualizarResumenBolson();
    }

    public function cargarDatos()
    {
        // Cargar solicitudes del usuario actual
        $this->solicitudes = TblSolicitudCompensa::with(['estado'])
            ->where('username', $this->username)
            ->orderByDesc('id')
            ->get();
    }

    public function actualizarResumenBolson()
    {
        try {
            $bolsonService = app(BolsonService::class);

            // Obtener saldo disponible
            $this->saldoDisponible = $bolsonService->obtenerSaldoDisponible($this->username);

            // Obtener resumen completo
            $this->resumenBolson = $bolsonService->obtenerResumenCompleto($this->username);

            // Determinar si puede compensar
            $this->puedeCompensar = $this->saldoDisponible > 0;

        } catch (\Exception $e) {
            Log::error("Error al actualizar resumen de bolsón", [
                'username' => $this->username,
                'error' => $e->getMessage()
            ]);

            $this->saldoDisponible = 0;
            $this->puedeCompensar = false;
        }
    }

    public function updatedHrsInicial()
    {
        $this->calcularMinutos();
        $this->calcularHoraFinal(); // Recalcular hora final si hay minutos solicitados
    }

    public function updatedHrsFinal()
    {
        $this->calcularMinutos();
    }

    public function updatedMinutosSolicitados()
    {
        $this->calcularHoraFinal();
    }

    private function calcularHoraFinal()
    {
        if ($this->hrs_inicial && $this->minutos_solicitados) {
            try {
                $inicio = Carbon::createFromTimeString($this->hrs_inicial);
                $final = $inicio->copy()->addMinutes($this->minutos_solicitados);
                $this->hrs_final = $final->format('H:i:s');
            } catch (\Exception $e) {
                // Si hay error, mantener la hora final actual
            }
        }
    }

    private function calcularMinutos()
    {
        if ($this->hrs_inicial && $this->hrs_final) {
            try {
                $inicio = Carbon::createFromTimeString($this->hrs_inicial);
                $fin = Carbon::createFromTimeString($this->hrs_final);

                // Si la hora final es menor que la inicial, asumimos que cruza medianoche
                if ($fin->lt($inicio)) {
                    $fin->addDay();
                }

                $this->minutos_solicitados = $inicio->diffInMinutes($fin);

            } catch (\Exception $e) {
                $this->minutos_solicitados = 0;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'fecha_solicitud' => 'required|date|after_or_equal:today',
            'hrs_inicial' => 'required',
            'hrs_final' => 'required',
            'minutos_solicitados' => 'required|integer|min:30', // Mínimo 30 minutos
            'observaciones' => 'nullable|string|max:500',
        ], [
            'fecha_solicitud.after_or_equal' => 'La fecha de compensación debe ser hoy o una fecha futura',
            'minutos_solicitados.min' => 'La compensación mínima es de 30 minutos',
        ]);

        // Validar que tenga saldo suficiente
        if ($this->minutos_solicitados > $this->saldoDisponible) {
            session()->flash('error',
                "Saldo insuficiente. Solicitado: {$this->minutos_solicitados} min, " .
                "Disponible: {$this->saldoDisponible} min"
            );
            return;
        }

        try {
            // Crear solicitud de compensación inicial
            $solicitud = TblSolicitudCompensa::create([
                'username' => $this->username,
                'cod_fiscalia' => $this->cod_fiscalia,
                'fecha_solicitud' => $this->fecha_solicitud,
                'hrs_inicial' => $this->hrs_inicial,
                'hrs_final' => $this->hrs_final,
                'minutos_solicitados' => $this->minutos_solicitados,
                'minutos_aprobados' => null,
                'id_estado' => 1, // Temporal, se cambiará inmediatamente
                'observaciones' => $this->observaciones,
                'aprobado_por' => null,
                'fecha_aprobacion' => null,
            ]);

            // Inmediatamente aplicar descuento usando workflow
            $flujoService = app(\App\Services\FlujoEstadoService::class);
            $estadoSolicitada = \App\Models\TblEstado::where('codigo', 'COMPENSACION_SOLICITADA')->first();

            if ($estadoSolicitada) {
                $resultado = $flujoService->ejecutarTransicionModelo(
                    $solicitud->id,
                    $estadoSolicitada->id,
                    $this->username,
                    "Solicitud de compensación - Descuento inmediato de {$this->minutos_solicitados} min",
                    'TblSolicitudCompensa'
                );

                if ($resultado['exitoso']) {
                    Log::info("Compensación solicitada con descuento inmediato", [
                        'solicitud_id' => $solicitud->id,
                        'username' => $this->username,
                        'minutos_descontados' => $this->minutos_solicitados,
                        'estado' => 'COMPENSACION_SOLICITADA'
                    ]);

                    session()->flash('mensaje',
                        "✅ Solicitud creada y minutos descontados del bolsón. " .
                        "Solicitado: {$this->minutos_solicitados} min (" .
                        number_format($this->minutos_solicitados/60, 1) . " horas). " .
                        "Pendiente de aprobación del jefe."
                    );
                } else {
                    // Si falla el descuento, eliminar la solicitud
                    $solicitud->delete();
                    session()->flash('error', 'Error al procesar descuento: ' . $resultado['mensaje']);
                    return;
                }
            } else {
                session()->flash('error', 'Estado COMPENSACION_SOLICITADA no encontrado');
                return;
            }

            // Limpiar formulario
            $this->reset([
                'fecha_solicitud', 'hrs_inicial', 'hrs_final',
                'minutos_solicitados', 'observaciones'
            ]);

            // Recargar datos
            $this->cargarDatos();
            $this->actualizarResumenBolson();

        } catch (\Exception $e) {
            Log::error("Error al crear solicitud de compensación", [
                'username' => $this->username,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Error al crear la solicitud: ' . $e->getMessage());
        }
    }

    public function simularDescuento()
    {
        if (!$this->minutos_solicitados || $this->minutos_solicitados <= 0) {
            session()->flash('warning', 'Ingrese minutos para simular');
            return;
        }

        try {
            $bolsonService = app(BolsonService::class);
            $simulacion = $bolsonService->simularDescuento($this->username, $this->minutos_solicitados);

            if ($simulacion['factible']) {
                $mensaje = "✅ Simulación exitosa: Se descontarían {$this->minutos_solicitados} min de {$simulacion['total_disponible']} min disponibles";
                session()->flash('info', $mensaje);
            } else {
                session()->flash('warning', "⚠️ " . $simulacion['mensaje']);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error en simulación: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sistema.ingreso-compensacion')
            ->layout('components.layouts.app');
    }
}
