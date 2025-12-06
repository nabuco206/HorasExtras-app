<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SolicitudesPagoExportController;

$user = App\Models\User::first();
if ($user) Auth::login($user);

$ctrl = new SolicitudesPagoExportController();
$request = new Illuminate\Http\Request(['search'=>null,'estadoId'=>null]);
$response = $ctrl->export($request);

// Capturar output
ob_start();
$response->sendContent();
$content = ob_get_clean();
file_put_contents('tmp_solicitudes_export.csv', $content);

echo "Wrote tmp_solicitudes_export.csv (" . strlen($content) . " bytes)\n";
