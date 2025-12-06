<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = new \App\Services\FlujoEstadoService();

// Parámetros de la transición observada
$flujoId = 2;
$estadoOrigen = 2;
$estadoDestino = 3;

echo "Validación con rol='udp'\n";
$val1 = $svc->validarTransicion($flujoId, $estadoOrigen, $estadoDestino, 'udp', \App\Models\TblSolicitudHe::find(1));
print_r($val1);

echo "\nValidación con rol=3 (numérico)\n";
$val2 = $svc->validarTransicion($flujoId, $estadoOrigen, $estadoDestino, 3, \App\Models\TblSolicitudHe::find(1));
print_r($val2);

echo "\nValidación con rol=2 (JD)\n";
$val3 = $svc->validarTransicion($flujoId, $estadoOrigen, $estadoDestino, 2, \App\Models\TblSolicitudHe::find(1));
print_r($val3);

