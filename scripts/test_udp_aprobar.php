<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = new \App\Services\FlujoEstadoService();
$res = $svc->ejecutarTransicionesMultiples([1], null, 'udp', 'Prueba UDP desde archivo');
print_r($res);

echo "\n";
