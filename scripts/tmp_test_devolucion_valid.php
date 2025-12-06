<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Services\BolsonService;
use Illuminate\Support\Facades\DB;

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bolsonService = $app->make(BolsonService::class);

$idComp = 1; // existing compensacion id from DB
$result = $bolsonService->crearBolsonDevolución('persona01', 12, 'Prueba devolución con id válido', $idComp);

echo "RESULT:\n";
print_r($result);

// show last history row
$last = DB::table('tbl_bolson_hists')->orderBy('created_at','desc')->first();

echo "LAST HIST:\n";
print_r($last);


