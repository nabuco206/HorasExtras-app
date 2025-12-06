<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\TblSolicitudCompensa;

// intentar usar primer usuario
$user = App\Models\User::first();
if ($user) Auth::login($user);

$query = TblSolicitudCompensa::query();
$query->whereIn('id_estado', [10,11]);

if ($user && intval($user->role_id) === 2) {
    $cod = $user->cod_fiscalia ?? null;
    if ($cod) $query->where('cod_fiscalia', $cod);
}

$rows = $query->orderBy('created_at','desc')->take(20)->get();
foreach ($rows as $r) {
    echo "{$r->id} | {$r->username} | {$r->cod_fiscalia} | {$r->created_at}\n";
}
