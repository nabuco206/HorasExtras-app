<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Obtener una compensación en estado COMPENSACION_SOLICITADA
$idSolicitada = DB::table('tbl_estados')->where('codigo', 'COMPENSACION_SOLICITADA')->value('id');
$sol = DB::table('tbl_solicitud_compensas')->where('id_estado', $idSolicitada)->first();
if (!$sol) {
    echo "No hay solicitudes en estado COMPENSACION_SOLICITADA para probar\n";
    exit;
}

echo "Probando aprobacion de compensacion id={$sol->id} username={$sol->username}\n";

$svc = new \App\Services\CompensacionService(app(\App\Services\BolsonService::class), app(\App\Services\FlujoEstadoService::class));
$res = $svc->procesarAprobacionCompensacion(\App\Models\TblSolicitudCompensa::find($sol->id), null, 'jd');
print_r($res);

