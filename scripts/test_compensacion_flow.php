<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// Crear usuario de prueba y bolson
$username = 'comp_user';
DB::table('tbl_personas')->insertOrIgnore(['id'=>999,'username'=>$username,'nombre'=>'Comp','apellido'=>'User','password'=>bcrypt('x'),'cod_fiscalia'=>1,'id_rol'=>1,'created_at'=>now(),'updated_at'=>now()]);

// Crear una solicitud HE de prueba para asociar al bolsón (estructura mínima)
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

// Crear bolsón con 120 min disponible asociado a la HE de prueba
$bolId = DB::table('tbl_bolson_tiempos')->insertGetId(['username'=>$username,'id_solicitud_he'=>$heId,'minutos'=>120,'saldo_min'=>120,'fecha_crea'=>date('Y-m-d'),'fecha_vence'=>date('Y-m-d',strtotime('+1 year')),'origen'=>'TEST','estado'=>'DISPONIBLE','activo'=>1,'created_at'=>now(),'updated_at'=>now()]);

echo "HE creado id={$heId} y Bolson creado id={$bolId} para {$username}\n";

// Crear solicitud de compensación (tipo compensacion) - se descontará al crear
$id = DB::table('tbl_solicitud_compensas')->insertGetId([
    'username' => $username,
    'cod_fiscalia' => 1,
    'fecha_solicitud' => date('Y-m-d', strtotime('+1 day')),
    'hrs_inicial' => '09:00',
    'hrs_final' => '11:00',
    'minutos_solicitados' => 120,
    'minutos_aprobados' => null,
    'id_estado' => 1,
    'observaciones' => 'Prueba flujo compensacion',
    'aprobado_por' => null,
    'fecha_aprobacion' => null,
    'created_at' => now(),
    'updated_at' => now()
]);

echo "Solicitud compensacion creada id={$id}\n";

// Ejecutar el flujo para descontar minutos (simulando lo que hace IngresoCompensacion)
$flujo = app(\App\Services\FlujoEstadoService::class);
$estadoSolicitada = \App\Models\TblEstado::where('codigo','COMPENSACION_SOLICITADA')->first();
$res = $flujo->ejecutarTransicionModelo($id, $estadoSolicitada->id, $username, 'Descuento inmediato test', 'TblSolicitudCompensa');
print_r($res);

// Verificar bolsón saldo
$bol = DB::table('tbl_bolson_tiempos')->where('id', $bolId)->first();
print_r($bol);

// Ahora aprobar como JD (username jd)
$compService = app(\App\Services\CompensacionService::class);
$sol = \App\Models\TblSolicitudCompensa::find($id);
$resAprob = $compService->procesarAprobacionCompensacion($sol, null, 'jd');
print_r($resAprob);

// Verificar estado final y bolson (no cambio al aprobar)
$solFinal = \App\Models\TblSolicitudCompensa::find($id);
echo "Estado final: {$solFinal->id_estado}\n";
$bolFinal = DB::table('tbl_bolson_tiempos')->where('id', $bolId)->first();
print_r($bolFinal);

// Ahora crear otra solicitud y simular rechazo
$id2 = DB::table('tbl_solicitud_compensas')->insertGetId([
    'username' => $username,
    'cod_fiscalia' => 1,
    'fecha_solicitud' => date('Y-m-d', strtotime('+2 day')),
    'hrs_inicial' => '09:00',
    'hrs_final' => '10:00',
    'minutos_solicitados' => 60,
    'minutos_aprobados' => null,
    'id_estado' => 1,
    'observaciones' => 'Prueba rechazo',
    'aprobado_por' => null,
    'fecha_aprobacion' => null,
    'created_at' => now(),
    'updated_at' => now()
]);
$res2 = $flujo->ejecutarTransicionModelo($id2, $estadoSolicitada->id, $username, 'Descuento inmediato test 2', 'TblSolicitudCompensa');
print_r($res2);

// Rechazar esta solicitud como jd
$sol2 = \App\Models\TblSolicitudCompensa::find($id2);
$resRech = $compService->procesarRechazoCompensacion($sol2, 'jd', 'Motivo prueba');
print_r($resRech);

// Verificar bolson saldo después de devolución
$bolAfter = DB::table('tbl_bolson_tiempos')->where('id', $bolId)->first();
print_r($bolAfter);

// Cleanup rápido (opcional)
DB::table('tbl_solicitud_compensas')->whereIn('id', [$id,$id2])->delete();
DB::table('tbl_bolson_tiempos')->where('id', $bolId)->delete();
DB::table('tbl_personas')->where('id', 999)->delete();

echo "Cleanup realizado\n";
