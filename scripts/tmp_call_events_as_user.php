<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CompensacionCalendarController;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// intentar autenticar usuario 1 si existe
try {
    $userModel = new App\Models\User();
    $user = App\Models\User::first();
    if ($user) {
        Auth::login($user);
    }
} catch (Throwable $e) {
    // ignore
}

$ctrl = new CompensacionCalendarController();
$request = new Illuminate\Http\Request();
$response = $ctrl->events($request);

echo $response->getContent();
