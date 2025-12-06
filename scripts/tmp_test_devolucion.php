<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\BolsonService;

$bolsonService = app(BolsonService::class);
$res = $bolsonService->crearBolsonDevolución('persona01', 12, 'Prueba devolución manual', 9999);
print_r($res);
