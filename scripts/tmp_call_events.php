<?php
require __DIR__.'/../vendor/autoload.php';

// Boot the framework
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/jd/compensaciones/events', 'GET');
$response = $kernel->handle($request);

echo $response->getContent();

$kernel->terminate($request, $response);

