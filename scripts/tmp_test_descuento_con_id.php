<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\TblSolicitudCompensa;

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Insertar una compensación de prueba
$id = DB::table('tbl_solicitud_compensas')->insertGetId([
    'username' => 'persona01',
    'cod_fiscalia' => 501,
    'fecha_solicitud' => now()->toDateString(),
    'hrs_inicial' => '18:00',
    'hrs_final' => '19:00',
    'minutos_solicitados' => 60,
    'minutos_aprobados' => null,
    'id_estado' => 1, // INGRESADO o id inicial
    'created_at' => now(),
    'updated_at' => now()
]);

echo "Creada compensacion id={$id}\n";

$sol = TblSolicitudCompensa::find($id);
$flujo = $app->make(\App\Services\FlujoEstadoService::class);

// Obtener id_ser SOLICITADA
$estadoSolicitada = \App\Models\TblEstado::where('codigo','COMPENSACION_SOLICITADA')->first();
if (!$estadoSolicitada) {
    echo "Estado SOLICITADA no encontrado\n";
    exit(1);
}

// Ejecutar transición para forzar descuento
$res = $flujo->ejecutarTransicionModelo($id, $estadoSolicitada->id, 'tester', null, 'TblSolicitudCompensa');

print_r($res);

$rows = DB::select("SELECT id,id_bolson_tiempo,username,id_solicitud_compensa,accion,minutos_afectados,observaciones,created_at FROM tbl_bolson_hists WHERE username='persona01' ORDER BY created_at DESC LIMIT 5");
print_r($rows);
