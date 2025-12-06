<?php

namespace App\Http\Controllers;

use App\Models\TblBolsonTiempo;
use App\Models\TblSolicitudHe;
use App\Models\TblSolicitudCompensa;
use App\Services\BolsonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        protected BolsonService $bolsonService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $username = $user->username ?? $user->name;

        // Datos del bolsón de tiempo (disponibles y pendientes)
        $resumenCompleto = $this->bolsonService->obtenerResumenCompleto($username);
        $saldoDisponible = $resumenCompleto['total_disponible'];
        $saldoPendiente = $resumenCompleto['total_pendiente'];
        $detalleBolson = $this->bolsonService->obtenerDetalleSaldo($username);

        // Solo trabajar con minutos
        $minutosDisponibles = $saldoDisponible;
        $minutosPendientes = $saldoPendiente;

        // Estadísticas de solicitudes HE del mes actual
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $solicitudesPendientes = TblSolicitudHe::where('username', $username)
            ->where('id_estado', 1) // Pendiente
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->count();

        $solicitudesAprobadas = TblSolicitudHe::where('username', $username)
            ->where('id_estado', 2) // Aprobada
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->count();

        $totalMinutosMes = TblSolicitudHe::where('username', $username)
            ->where('id_estado', 2) // Solo aprobadas
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('total_min');

        // Mantener solo minutos
        $totalMinutosMes = $totalMinutosMes;

        // Usar el nuevo método del servicio para obtener resumen detallado
        $resumenBolson = $this->bolsonService->obtenerResumenDetallado($username);
        $bolsonesProximosVencer = count($resumenBolson['bolsones_proximos_vencer']);
        $detallesBolsonesProximos = $resumenBolson['bolsones_proximos_vencer'];

        // Compensaciones del mes
        $compensacionesMes = TblSolicitudCompensa::where('username', $username)
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->count();

        // Nuevas variables para la vista
        $solicitudesBolson = TblSolicitudHe::where('username', $username)
            ->where('afecta_bolson', true)
            ->get();

        $ultimasSolicitudes = TblSolicitudHe::where('username', $username)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $compensaciones = TblSolicitudCompensa::where('username', $username)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact(
            'saldoDisponible',
            'minutosDisponibles',
            'minutosPendientes',
            'saldoPendiente',
            'detalleBolson',
            'solicitudesPendientes',
            'solicitudesAprobadas',
            'totalMinutosMes',
            'bolsonesProximosVencer',
            'detallesBolsonesProximos',
            'compensacionesMes',
            'resumenBolson',
            'resumenCompleto',
            'solicitudesBolson',
            'ultimasSolicitudes',
            'compensaciones'
        ));
    }
}
