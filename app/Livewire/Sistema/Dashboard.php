<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\BolsonService;
use App\Models\TblSolicitudHe;
use App\Models\TblSolicitudCompensa;
use App\Models\TblPersona;
use App\Models\TblBolsonTiempo;
use Carbon\Carbon;

class Dashboard extends Component
{
    // Control para mostrar el bolsón (solo rol 1)
    public bool $mostrarBolson = false;

    // Variables que usa la vista `resources/views/dashboard.blade.php`
    public int $minutosDisponibles = 0;
    public int $minutosPendientes = 0;
    public array $detalleBolson = [];
    public array $resumenCompleto = [];
    public int $bolsonesProximosVencer = 0;

    public int $solicitudesPendientes = 0;
    public int $solicitudesAprobadas = 0;
    public int $totalMinutosMes = 0;
    public $ultimasSolicitudes = [];
    public $compensaciones = [];
    public $solicitudesBolson = [];
    public $grillaSolicitudes = []; // Nueva variable para la grilla

    public function mount(): void
    {
        $user = Auth::user();
        $rol = $user->id_rol;
        $username = $user->username ?? $user->name;
        $codFiscalia = $user->cod_fiscalia ?? null;

        $inicioAnio = Carbon::now()->startOfYear();
        $finAnio = Carbon::now()->endOfYear();

        // Mostrar bolsón solo para rol 1 (Usuario Normal)
        $this->mostrarBolson = ($user->id_rol ?? null) == 1;

        // Cargar datos del bolsón solo si corresponde
        if ($this->mostrarBolson) {
            $bolsonService = app(BolsonService::class);
            $this->resumenCompleto = $bolsonService->obtenerResumenCompleto($username);
            $this->minutosDisponibles = $this->resumenCompleto['total_disponible'] ?? 0;
            $this->minutosPendientes = $this->resumenCompleto['total_pendiente'] ?? 0;
            $this->detalleBolson = $bolsonService->obtenerDetalleSaldo($username);

            $this->bolsonesProximosVencer = TblBolsonTiempo::where('username', $username)
                ->where('activo', true)
                ->where('estado', 'DISPONIBLE')
                ->where('saldo_min', '>', 0)
                ->where('fecha_vence', '<=', Carbon::now()->addDays(30))
                ->where('fecha_vence', '>=', Carbon::now())
                ->count();
        } else {
            $this->resumenCompleto = ['detalle_pendientes' => []];
            $this->detalleBolson = [];
            $this->minutosDisponibles = 0;
            $this->minutosPendientes = 0;
            $this->bolsonesProximosVencer = 0;
        }

        // Base query para solicitudes HE
        $heQuery = TblSolicitudHe::query()
            ->where('username', $username)
            ->whereIn('id_estado', [6, 5]);

        // Base query para compensaciones
        $compQuery = TblSolicitudCompensa::query()
            ->where('username', $username)
            ->whereIn('id_estado', [10]);

        // Filtrar por fiscalía si es JD
        if ($rol == 2 && $codFiscalia) {
            $heQuery->where('cod_fiscalia', $codFiscalia);
            $compQuery->where('cod_fiscalia', $codFiscalia);
        }

        // Suma de minutos aprobados en HE
        $minutosHeAprobados = (int) $heQuery->sum('total_min');

        // Suma de minutos aprobados en compensaciones
        // $minutosCompensados = (int) $compQuery->sum('minutos_compensados');
        $minutosCompensados = 0;

        // Total minutos extras (HE + compensaciones)
        $this->totalMinutosMes = $minutosHeAprobados + $minutosCompensados;

        $this->solicitudesPendientes = $heQuery->count();
        $this->solicitudesAprobadas = $heQuery->where('id_estado', 2)->count();

        // Últimos 10 ingresos de solicitudes
        $this->ultimasSolicitudes = $heQuery->latest('created_at')->take(10)->get();

        // Últimos 10 ingresos de compensaciones
        $this->compensaciones = $compQuery->latest('created_at')->take(10)->get();

        // Consulta para la grilla de solicitudes (últimas 10 solicitudes con todos los campos necesarios)
        $this->grillaSolicitudes = TblSolicitudHe::query()
            ->where('username', $username)
            ->with('idEstado') // Cargar la relación con tbl_estado
            ->latest('created_at')
            ->take(10)
            ->get([
                'id',
                'id_tipo_trabajo',
                'fecha',
                'hrs_inicial',
                'hrs_final',
                'id_estado', // ID del estado
                'id_tipo_compensacion',
                'min_reales',
                'min_25',
                'min_50',
                'total_min',
                // 'documento',
            ]);
    }

    public function render()
    {
        // La vista existente `resources/views/dashboard.blade.php` usa las variables públicas arriba
    
        return view('dashboard');
    }
}
