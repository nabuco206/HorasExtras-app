<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TblSolicitudHe;

$rows = TblSolicitudHe::select('id','username','id_estado','id_tipo_compensacion','cod_fiscalia','created_at')
    ->orderBy('id','desc')
    ->take(50)
    ->get();

foreach ($rows as $r) {
    echo sprintf("id=%s username=%s estado=%s tipo=%s fiscalia=%s created=%s\n",
        $r->id, $r->username, $r->id_estado, $r->id_tipo_compensacion, $r->cod_fiscalia, $r->created_at);
}
