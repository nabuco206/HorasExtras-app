<?php

namespace App\Http\Livewire\Sistema;

use Livewire\Component;
use App\Services\FlujoEstadoService;
use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AprobacionesMasivas extends Component
{
    public $tipos_trabajo = [];
    public $estados = [];
    public $solicitudes = [];
    public $seleccionados = [];
    public $selectAll = false;
    public $mostrarSoloPendientes = true;

    // Para mostrar resultados
    public $ultimaOperacion = null;
    public $mostrarResultados = false;

    public function mount()
    {
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
        $this->cargarSolicitudes();
    }

    public function cargarSolicitudes()
    {
        $query = TblSolicitudHe::with(['tipoTrabajo', 'estado'])
            ->orderByDesc('id');

        if ($this->mostrarSoloPendientes) {
            $query->where('id_estado', 1); // Solo INGRESADO
        }

        $this->solicitudes = $query->get();
        $this->reset(['seleccionados', 'selectAll']);
    }

    public function updatedMostrarSoloPendientes()
    {
        $this->cargarSolicitudes();
    }

    public function aprobarSeleccionados()
    {
        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debe seleccionar al menos una solicitud');
            return;
        }

        try {
            $flujoService = app(FlujoEstadoService::class);

            // Obtener usuario autenticado
            $usuarioId = Auth::id() ?? 1; // Fallback a 1 si no hay usuario autenticado

            Log::info("Iniciando aprobación masiva", [
                'seleccionados' => $this->seleccionados,
                'usuario_id' => $usuarioId,
                'total_seleccionados' => count($this->seleccionados)
            ]);

            // Usar el nuevo método de aprobaciones masivas
            $resultado = $flujoService->ejecutarTransicionesMultiples(
                $this->seleccionados,
                3, // APROBADO_JEFE
                $usuarioId,
                'Aprobación masiva desde interfaz'
            );

            Log::info("Resultado de aprobación masiva", [
                'exitoso' => $resultado['exitoso'],
                'procesadas' => $resultado['procesadas'] ?? 0,
                'bolsones_creados' => count($resultado['bolsones_creados'] ?? []),
                'mensaje' => $resultado['mensaje'] ?? 'Sin mensaje'
            ]);

            if ($resultado['exitoso']) {
                $this->ultimaOperacion = $resultado;
                $this->mostrarResultados = true;

                session()->flash('mensaje', $resultado['mensaje']);

                // Recargar datos
                $this->cargarSolicitudes();

                Log::info("Aprobación masiva completada desde interfaz", [
                    'procesadas' => $resultado['procesadas'],
                    'bolsones_creados' => count($resultado['bolsones_creados']),
                    'seleccionados' => $this->seleccionados
                ]);

            } else {
                session()->flash('error', $resultado['mensaje']);
                Log::warning("Aprobación masiva falló", [
                    'resultado' => $resultado,
                    'seleccionados' => $this->seleccionados
                ]);
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar aprobaciones: ' . $e->getMessage());
            Log::error("Error en aprobación masiva desde interfaz", [
                'error' => $e->getMessage(),
                'seleccionados' => $this->seleccionados
            ]);
        }
    }

    public function rechazarSeleccionados()
    {
        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debe seleccionar al menos una solicitud');
            return;
        }

        try {
            $flujoService = app(FlujoEstadoService::class);

            // Aquí usaríamos el estado de RECHAZADO si existiera
            // Por ahora comentamos esta funcionalidad
            session()->flash('warning', 'Funcionalidad de rechazo masivo pendiente de implementar');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar rechazos: ' . $e->getMessage());
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->seleccionados = $this->solicitudes->pluck('id')->toArray();
        } else {
            $this->seleccionados = [];
        }
    }

    public function updatedSeleccionados()
    {
        $this->selectAll = count($this->seleccionados) === count($this->solicitudes) && count($this->solicitudes) > 0;
    }

    public function cerrarResultados()
    {
        $this->mostrarResultados = false;
        $this->ultimaOperacion = null;
    }

    // Método de prueba para debuggear desde la interfaz
    public function probarAprobacion()
    {
        Log::info("Método probarAprobacion llamado");

        try {
            $solicitudesPendientes = TblSolicitudHe::where('id_estado', 1)->take(2)->pluck('id')->toArray();

            Log::info("Solicitudes encontradas para prueba", ['ids' => $solicitudesPendientes]);

            if (empty($solicitudesPendientes)) {
                session()->flash('warning', 'No hay HE pendientes para probar');
                return;
            }

            $flujoService = app(FlujoEstadoService::class);
            $resultado = $flujoService->ejecutarTransicionesMultiples(
                $solicitudesPendientes,
                3,
                Auth::id() ?? 1,
                'Prueba desde interfaz web'
            );

            Log::info("Resultado de prueba", ['resultado' => $resultado]);

            if ($resultado['exitoso']) {
                session()->flash('mensaje', 'Prueba exitosa: ' . $resultado['mensaje']);
                $this->cargarSolicitudes();
            } else {
                session()->flash('error', 'Prueba falló: ' . $resultado['mensaje']);
            }

        } catch (\Exception $e) {
            Log::error("Error en prueba de aprobación", ['error' => $e->getMessage()]);
            session()->flash('error', 'Error en prueba: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.sistema.aprobaciones-masivas')
            ->layout('components.layouts.app');
    }
}
