<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Crear una solicitud HE de tipo DINERO (id_tipo_compensacion = 2)
$id = DB::table('tbl_solicitud_hes')->insertGetId([
    'username' => 'test_dinero',
    'cod_fiscalia' => 1,
    'id_tipo_trabajo' => 1,
    'fecha' => date('Y-m-d'),
    'hrs_inicial' => '10:00:00',
    'hrs_final' => '12:00:00',
    'id_estado' => 2, // estado que permite aprobación en el flujo
    'id_tipo_compensacion' => 2, // DINERO
    'min_reales' => 120,
    'min_25' => 0,
    'min_50' => 0,
    'total_min' => 120,
    'created_at' => now(),
    'updated_at' => now()
]);

echo "Solicitud creada id={$id}\n";

// Ejecutar la aprobación como 'udp'
$svc = new \App\Services\FlujoEstadoService();
$res = $svc->ejecutarTransicionesMultiples([$id], null, 'udp', 'Prueba DINERO no crear bolsón');
print_r($res);

// Verificar si se creó algún bolsón para esa solicitud
$bolson = DB::table('tbl_bolson_tiempos')->where('id_solicitud_he', $id)->first();
if ($bolson) {
    echo "Bolsón encontrado para solicitud: "; print_r($bolson);
} else {
    echo "No se creó bolsón para solicitud DINERO (esperado).\n";
}

// Cleanup: eliminar solicitud y posible seguimiento
DB::table('tbl_seguimiento_solicituds')->where('id_solicitud_he', $id)->delete();
DB::table('tbl_solicitud_hes')->where('id', $id)->delete();

echo "Cleanup realizado.\n";
