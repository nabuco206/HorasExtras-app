<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Obtener resumen de fiscalías con tiempos acumulados por tipo de compensación
     *
     * @param bool $esJD Si el usuario es JD (rol 2)
     * @param string|null $codFiscaliaUsuario Código de fiscalía del usuario (para JD)
     * @return \Illuminate\Support\Collection
     */
    
    public function obtenerResumenFiscalias($esJD = false, $codFiscaliaUsuario = null)
    {
        $inicioAnio = Carbon::now()->startOfYear();
        $finAnio = Carbon::now()->endOfYear();

        // Obtener resumen para tipo 1 (compensación) - desde solicitudes
        $queryTipo1 = DB::table('tbl_solicitud_hes')
            ->join('tbl_personas', 'tbl_solicitud_hes.username', '=', 'tbl_personas.username')
            ->join('tbl_fiscalias', 'tbl_personas.cod_fiscalia', '=', 'tbl_fiscalias.cod_fiscalia')
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 1)
            ->where('tbl_solicitud_hes.id_estado', 6)
            ->whereBetween('tbl_solicitud_hes.created_at', [$inicioAnio, $finAnio]);

        if ($esJD && $codFiscaliaUsuario) {
            $queryTipo1->where('tbl_personas.cod_fiscalia', $codFiscaliaUsuario);
        }

        $tipo1 = $queryTipo1->select(
                'tbl_fiscalias.cod_fiscalia',
                'tbl_fiscalias.gls_fiscalia',
                DB::raw('1 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_solicitud_hes.total_min) as total_minutos')
            )
            ->groupBy('tbl_fiscalias.cod_fiscalia', 'tbl_fiscalias.gls_fiscalia')
            ->get();

        // Obtener resumen para tipo 2 (dinero) - desde solicitudes aprobadas
        $queryTipo2 = DB::table('tbl_solicitud_hes')
            ->join('tbl_personas', 'tbl_solicitud_hes.username', '=', 'tbl_personas.username')
            ->join('tbl_fiscalias', 'tbl_personas.cod_fiscalia', '=', 'tbl_fiscalias.cod_fiscalia')
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
            ->where('tbl_solicitud_hes.id_estado', 5)
            ->whereBetween('tbl_solicitud_hes.created_at', [$inicioAnio, $finAnio]);

        if ($esJD && $codFiscaliaUsuario) {
            $queryTipo2->where('tbl_personas.cod_fiscalia', $codFiscaliaUsuario);
        }

        $tipo2 = $queryTipo2->select(
                'tbl_fiscalias.cod_fiscalia',
                'tbl_fiscalias.gls_fiscalia',
                DB::raw('2 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_solicitud_hes.total_min) as total_minutos')
            )
            ->groupBy('tbl_fiscalias.cod_fiscalia', 'tbl_fiscalias.gls_fiscalia')
            ->get();

        // Combinar y agrupar por cod_fiscalia
        return $tipo1->concat($tipo2)->groupBy('cod_fiscalia');
    }

    /**
     * Obtener detalle de usuarios por fiscalía
     *
     * @param string $codFiscalia Código de la fiscalía
     * @param bool $esJD Si el usuario es JD
     * @param string|null $codFiscaliaUsuario Código de fiscalía del usuario
     * @return \Illuminate\Support\Collection
     */
    public function obtenerDetalleFiscalia($codFiscalia, $esJD = false, $codFiscaliaUsuario = null)
    {
        $inicioAnio = Carbon::now()->startOfYear();
        $finAnio = Carbon::now()->endOfYear();

        // Obtener detalle para tipo 1 (compensación) - desde solicitudes
        $queryTipo1 = DB::table('tbl_solicitud_hes')
            ->join('tbl_personas', 'tbl_solicitud_hes.username', '=', 'tbl_personas.username')
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_personas.cod_fiscalia', $codFiscalia)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 1)
            ->where('tbl_solicitud_hes.id_estado', 6)
            ->whereBetween('tbl_solicitud_hes.created_at', [$inicioAnio, $finAnio]);

        if ($esJD && $codFiscaliaUsuario) {
            $queryTipo1->where('tbl_personas.cod_fiscalia', $codFiscaliaUsuario);
        }

        $tipo1 = $queryTipo1->select(
                'tbl_personas.nombre',
                'tbl_personas.apellido',
                'tbl_personas.username',
                DB::raw('1 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_solicitud_hes.total_min) as total_minutos')
            )
            ->groupBy('tbl_personas.nombre', 'tbl_personas.apellido', 'tbl_personas.username')
            ->get();

        // Obtener detalle para tipo 2 (dinero) - desde solicitudes
        $queryTipo2 = DB::table('tbl_solicitud_hes')
            ->join('tbl_personas', 'tbl_solicitud_hes.username', '=', 'tbl_personas.username')
            ->where('tbl_personas.flag_activo', true)
            ->where('tbl_personas.id_rol', 1)
            ->where('tbl_personas.cod_fiscalia', $codFiscalia)
            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
            ->where('tbl_solicitud_hes.id_estado', 5)
            ->whereBetween('tbl_solicitud_hes.created_at', [$inicioAnio, $finAnio]);

        if ($esJD && $codFiscaliaUsuario) {
            $queryTipo2->where('tbl_personas.cod_fiscalia', $codFiscaliaUsuario);
        }

        $tipo2 = $queryTipo2->select(
                'tbl_personas.nombre',
                'tbl_personas.apellido',
                'tbl_personas.username',
                DB::raw('2 as id_tipo_compensacion'),
                DB::raw('SUM(tbl_solicitud_hes.total_min) as total_minutos')
            )
            ->groupBy('tbl_personas.nombre', 'tbl_personas.apellido', 'tbl_personas.username')
            ->get();

        // Combinar y agrupar por username
        return $tipo1->concat($tipo2)->groupBy('username');
    }

    /**
     * Obtener estadísticas de solicitudes pendientes según el rol del usuario
     *
     * @param int $rol ID del rol del usuario
     * @param string|null $username Username del usuario (para rol 1)
     * @param string|null $codFiscalia Código de fiscalía (para rol 2)
     * @return array
     */
    public function obtenerEstadisticasPendientes($rol, $username = null, $codFiscalia = null, $tipoCompensa = null)
    {
        // Inicializar variables
        $pendientesComp = 0;
        $rechazadosComp = 0;
        $pendientesPago = 0;
        $aprobadasHE = 0;
        $totalPago = 0;
        $sumaMinHe = 0;
        $cantidadSolicitudesCompensa = 0;
        $esLider = $username->flag_lider ?? false;
        if($esLider){
            $rol = 6;
        }

        $inicioAnio = Carbon::now()->startOfYear();
        $finAnio = Carbon::now()->endOfYear();
        
        
        $queryBase = DB::table('tbl_solicitud_hes') ;
                            // ->whereBetween('tbl_solicitud_hes.created_at', [$inicioAnio, $finAnio]);
        $queryBaseSolicitudCompensa = DB::table('tbl_solicitud_compensas') ;


            switch ($rol) {
            case 3:     
                $pendientesPago = (clone $queryBase)
                    ->where('tbl_solicitud_hes.id_estado', 2)
                    ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
                    ->count();  
            case 1:         
                    $pendientesComp = (clone $queryBase)
                            ->where('tbl_solicitud_hes.username', $username)
                            ->where('tbl_solicitud_hes.id_estado', 1)
                            ->count();   
                    $rechazadosComp = (clone $queryBase)
                            ->where('tbl_solicitud_hes.username', $username)
                            ->where('tbl_solicitud_hes.id_estado', 4)
                            ->count();           
                    break;   
            case 2:            
                $pendientesComp = (clone $queryBase)
                        ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia)
                        ->where('tbl_solicitud_hes.id_estado', 1)
                        ->where('tbl_solicitud_hes.id_tipo_compensacion', 1)
                        ->count();
                $pendientesPago = (clone $queryBase)
                        ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia)
                        ->where('tbl_solicitud_hes.id_estado', 1)
                        ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
                        ->count();        

                $aprobadasHE  = (clone $queryBase)
                    ->where('tbl_solicitud_hes.username', $username)
                    ->where('tbl_solicitud_hes.id_estado', 6)
                    ->where('tbl_solicitud_hes.id_tipo_compensacion', $tipoCompensa)                              
                    ->count();

                $totalPago = (clone $queryBase)
                    ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia)
                    ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)   
                    ->count();   

                
                $sumaMinHe = (clone $queryBase)   
                            ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia)
                            ->where('tbl_solicitud_hes.id_estado', 6)                            
                            ->sum('total_min');   
                         
                   
                $totalPago = (clone $queryBase)
                            ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia) 
                            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)   
                            ->count(); 
                $cantidadSolicitudesCompensa = (clone $queryBaseSolicitudCompensa)
                            ->where('tbl_solicitud_compensas.cod_fiscalia', $codFiscalia) 
                            ->where('tbl_solicitud_compensas.id_estado', 9)   
                            ->count();


                break;
            case 4:
                $pendientesComp = (clone $queryBase)
                            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
                            ->where('tbl_solicitud_hes.id_estado', 3)
                            ->count();  
                $totalPago = (clone $queryBase)
                            ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)   
                            ->count();                 
                break;
            case 5:
              
                $pendientesComp = (clone $queryBase)
                        ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
                        ->where('tbl_solicitud_hes.id_estado', 4)
                        ->count();  
                $totalPago = (clone $queryBase)
                        ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)   
                        ->count();                
                break;   
            case 6:
                $pendientesComp = (clone $queryBase)
                    ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia)
                    ->where('tbl_solicitud_hes.id_estado', 1)
                    ->where('tbl_solicitud_hes.id_tipo_compensacion', 1)
                    ->count();
                $pendientesPago = (clone $queryBase)
                    ->where('tbl_solicitud_hes.cod_fiscalia', $codFiscalia)
                    ->where('tbl_solicitud_hes.id_estado', 1)
                    ->where('tbl_solicitud_hes.id_tipo_compensacion', 2)
                    ->count();     

                $cantidadSolicitudesCompensa = (clone $queryBaseSolicitudCompensa)
                            ->where('tbl_solicitud_compensas.cod_fiscalia', $codFiscalia) 
                            ->where('tbl_solicitud_compensas.id_estado', 9)   
                            ->count();    
    
    
                    break;     
                // return response()->json(['mensaje' => 'Opción no reconocida.'], 404);
        }
        

    
        return [
            'pendientesComp' => $pendientesComp,
            'pendientesPago' => $pendientesPago,
            'rechazadosComp' => $rechazadosComp,
            'aprobadasHE' => $aprobadasHE,
            'totalPago' => $totalPago,
            'sumaMinHe' => $sumaMinHe,
            'cantidadSolicitudesCompensa' => $cantidadSolicitudesCompensa,
        ];
    }
}