<?php

namespace App\Livewire\Sistema;

use Livewire\Component;
use App\Models\TblBolsonTiempo;
use App\Models\TblFiscalia;
use App\Models\TblPersona;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardTiempo extends Component
{
    public $tieneAcceso = false;
    public $resumenFiscalias = [];
    public $detalleFiscalia = null;
    public $detalleUsuarios = [];

    public function mount()
    {
        $user = Auth::user();

        // Verificar si el usuario tiene rol UDP (3), JUDP (4) o DER (5)
        $rolesPermitidos = [3, 4, 5]; // UDP, JUDP, DER
        $this->tieneAcceso = in_array($user->id_rol, $rolesPermitidos);

        if (!$this->tieneAcceso) {
            return;
        }

        $this->cargarResumen();
    }

    private function cargarResumen()
    {
        // Obtener resumen para tipo 1 (compensación) - desde bolsones
        $tipo1 = DB::table('tbl_bolson_tiempos')
            ->join('tbl_personas', 'tbl_bolson_tiempos.username', '=', 'tbl_personas.username')
            ->join('tbl_fiscalias', 'tbl_personas.cod_fiscalia', '=', 'tbl_fiscalias.cod_fiscalia')
            ->join('tbl_solicitud_hes', 'tbl_bolson_tiempos.id_solicitud_he', '=', 'tbl_solicitud_hes.id')
            ->where('tbl_bolson_tiempos.activo', true)
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 1)
            ->where('tbl_solicitud_hes.id_estado', 4)
            ->select(
                'tbl_fiscalias.cod_fiscalia',
                'tbl_fiscalias.gls_fiscalia',
                DB::raw('1 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_bolson_tiempos.saldo_min) as total_minutos')
            )
            ->groupBy('tbl_fiscalias.cod_fiscalia', 'tbl_fiscalias.gls_fiscalia')
            ->get();

        // Obtener resumen para tipo 2 (dinero) - desde solicitudes aprobadas
        $tipo2 = DB::table('tbl_solicitud_hes')
            ->join('tbl_personas', 'tbl_solicitud_hes.username', '=', 'tbl_personas.username')
            ->join('tbl_fiscalias', 'tbl_personas.cod_fiscalia', '=', 'tbl_fiscalias.cod_fiscalia')
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
            ->where('tbl_solicitud_hes.id_estado', 5)
            ->select(
                'tbl_fiscalias.cod_fiscalia',
                'tbl_fiscalias.gls_fiscalia',
                DB::raw('2 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_solicitud_hes.total_min) as total_minutos')
            )
            ->groupBy('tbl_fiscalias.cod_fiscalia', 'tbl_fiscalias.gls_fiscalia')
            ->get();

        // Combinar y agrupar por cod_fiscalia
        $this->resumenFiscalias = $tipo1->concat($tipo2)->groupBy('cod_fiscalia');
    }

    public function mostrarDetalle($codFiscalia)
    {
        $this->detalleFiscalia = $codFiscalia;

        // Obtener detalle para tipo 1 (compensación) - desde bolsones
        $tipo1 = DB::table('tbl_bolson_tiempos')
            ->join('tbl_personas', 'tbl_bolson_tiempos.username', '=', 'tbl_personas.username')
            ->join('tbl_solicitud_hes', 'tbl_bolson_tiempos.id_solicitud_he', '=', 'tbl_solicitud_hes.id')
            ->where('tbl_bolson_tiempos.activo', true)
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_personas.cod_fiscalia', $codFiscalia)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 1)
            ->where('tbl_solicitud_hes.id_estado', 4)
            ->select(
                'tbl_personas.nombre',
                'tbl_personas.apellido',
                'tbl_personas.username',
                DB::raw('1 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_bolson_tiempos.saldo_min) as total_minutos')
            )
            ->groupBy('tbl_personas.nombre', 'tbl_personas.apellido', 'tbl_personas.username')
            ->get();

        // Obtener detalle para tipo 2 (dinero) - desde solicitudes
        $tipo2 = DB::table('tbl_solicitud_hes')
            ->join('tbl_personas', 'tbl_solicitud_hes.username', '=', 'tbl_personas.username')
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_personas.cod_fiscalia', $codFiscalia)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
            ->where('tbl_solicitud_hes.id_estado', 5)
            ->select(
                'tbl_personas.nombre',
                'tbl_personas.apellido',
                'tbl_personas.username',
                DB::raw('2 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_solicitud_hes.total_min) as total_minutos')
            )
            ->groupBy('tbl_personas.nombre', 'tbl_personas.apellido', 'tbl_personas.username')
            ->get();

        // Combinar y agrupar por username
        $this->detalleUsuarios = $tipo1->concat($tipo2)->groupBy('username');
    }

    public function cerrarDetalle()
    {
        $this->detalleFiscalia = null;
        $this->detalleUsuarios = [];
    }

    public function render()
    {
        return view('livewire.sistema.dashboard-tiempo')
            ->layout('components.layouts.app');
    }
}
