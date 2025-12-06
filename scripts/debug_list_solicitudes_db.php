<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TblSolicitudCompensa;

$rows = TblSolicitudCompensa::all();
if ($rows->isEmpty()) {
    echo "No hay filas en tbl_solicitud_compensas\n";
    exit;
}

foreach ($rows as $r) {
    $tipo = isset($r->id_tipo_compensacion) ? $r->id_tipo_compensacion : '(no id_tipo_compensacion)';
    $estado = $r->id_estado ?? '(no id_estado)';
    echo "id={$r->id} username={$r->username} cod_fiscalia={$r->cod_fiscalia} id_estado={$estado} id_tipo_compensacion={$tipo} fecha={$r->created_at}\n";
}
