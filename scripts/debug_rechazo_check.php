<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$username = 'comp_user_dbg';
DB::table('tbl_personas')->insertOrIgnore(['id'=>1001,'username'=>$username,'nombre'=>'CompDbg','apellido'=>'User','password'=>bcrypt('x'),'cod_fiscalia'=>1,'id_rol'=>1,'created_at'=>now(),'updated_at'=>now()]);

// crear bolsón inicial con 120 min
$heId = DB::table('tbl_solicitud_hes')->insertGetId([
    'username' => $username,
    'cod_fiscalia' => 1,
    'id_tipo_trabajo' => 1,
    'fecha' => date('Y-m-d'),
    'hrs_inicial' => '08:00',
    'hrs_final' => '10:00',
    'id_estado' => 1,
    'id_tipo_compensacion' => 1,
    'min_reales' => 120,
    'min_25' => 0,
    'min_50' => 120,
    'total_min' => 120,
    'created_at' => now(),
    'updated_at' => now()
]);
$bolId = DB::table('tbl_bolson_tiempos')->insertGetId(['username'=>$username,'id_solicitud_he'=>$heId,'minutos'=>120,'saldo_min'=>120,'fecha_crea'=>date('Y-m-d'),'fecha_vence'=>date('Y-m-d',strtotime('+1 year')),'origen'=>'TEST','estado'=>'DISPONIBLE','activo'=>1,'created_at'=>now(),'updated_at'=>now()]);

echo "HE id={$heId}, bolson id={$bolId}\n";

// crear solicitud de compensacion (esto descontará minutos al crear)
$id = DB::table('tbl_solicitud_compensas')->insertGetId([
    'username' => $username,
    'cod_fiscalia' => 1,
    'fecha_solicitud' => date('Y-m-d', strtotime('+1 day')),
    'hrs_inicial' => '09:00',
    'hrs_final' => '11:00',
    'minutos_solicitados' => 120,
    'minutos_aprobados' => null,
    'id_estado' => 1,
    'observaciones' => 'Prueba debug rechazo',
    'aprobado_por' => null,
    'fecha_aprobacion' => null,
    'created_at' => now(),
    'updated_at' => now()
]);

echo "Solicitud comp id={$id}\n";

$flujo = app(\App\Services\FlujoEstadoService::class);
$estadoSolicitada = \App\Models\TblEstado::where('codigo','COMPENSACION_SOLICITADA')->first();
$res = $flujo->ejecutarTransicionModelo($id, $estadoSolicitada->id, $username, 'Descuento debug', 'TblSolicitudCompensa');
print_r($res);

// mostrar bolsones actuales
$bols = DB::table('tbl_bolson_tiempos')->where('username',$username)->get();
print_r($bols->toArray());

// ejecutar rechazo via CompensacionService
$compServ = app(\App\Services\CompensacionService::class);
$sol = \App\Models\TblSolicitudCompensa::find($id);
$resRech = $compServ->procesarRechazoCompensacion($sol, 'jd', 'Debug motivo');
print_r($resRech);

// mostrar bolsones tras rechazo
$bolsAfter = DB::table('tbl_bolson_tiempos')->where('username',$username)->get();
print_r($bolsAfter->toArray());

// listar bolsos de origen DEVOLUCION_COMPENSACION
$dev = DB::table('tbl_bolson_tiempos')->where('username',$username)->where('origen','DEVOLUCION_COMPENSACION')->get();
print_r(['devoluciones'=>$dev->toArray()]);

// cleanup
DB::table('tbl_solicitud_compensas')->where('id',$id)->delete();
DB::table('tbl_bolson_tiempos')->where('username',$username)->delete();
DB::table('tbl_solicitud_hes')->where('id',$heId)->delete();
DB::table('tbl_personas')->where('id',1001)->delete();

echo "Cleanup realizado\n";
