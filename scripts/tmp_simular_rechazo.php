<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\FlujoEstadoService;
use Illuminate\Support\Facades\DB;

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$flujo = $app->make(FlujoEstadoService::class);

// Cargar compensación existente
$comp = \App\Models\TblSolicitudCompensa::find(1);
if (!$comp) {
    echo "Compensacion id=1 no encontrada\n";
    exit(1);
}

// Usar reflection para invocar método privado devolverMinutosAlBolson
$ref = new ReflectionClass($flujo);
$method = $ref->getMethod('devolverMinutosAlBolson');
$method->setAccessible(true);

// Ejecutar
try {
    $method->invokeArgs($flujo, [$comp, $app->make(\App\Services\BolsonService::class)]);
    echo "Invocado devolverMinutosAlBolson para compensacion id={$comp->id}\n";
} catch (Exception $e) {
    echo "Error invoking: " . $e->getMessage() . "\n";
    exit(1);
}

// Mostrar últimas 3 filas de historial
$rows = DB::select("SELECT id,id_bolson_tiempo,username,id_solicitud_compensa,accion,minutos_afectados,observaciones,created_at FROM tbl_bolson_hists ORDER BY created_at DESC LIMIT 3");
print_r($rows);
