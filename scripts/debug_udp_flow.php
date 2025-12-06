<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = new \App\Services\FlujoEstadoService();
$db = \Illuminate\Support\Facades\DB::connection();

$solicitud = \App\Models\TblSolicitudHe::find(1);
if (! $solicitud) { echo "Solicitud 1 no encontrada\n"; exit(1); }

$estadoOrigenId = $solicitud->id_estado;
$estado = \App\Models\TblEstado::find($estadoOrigenId);

echo "Solicitud 1: username={$solicitud->username}, id_estado={$estadoOrigenId}\n";

$flujoId = (new ReflectionClass($svc))->getMethod('resolveFlujoIdFromEstado')->invoke($svc, $estado);

echo "Flujo resuelto: "; var_export($flujoId); echo "\n";

$trans = $svc->obtenerSiguientesTransicionesPorEstadoOrigen($estadoOrigenId, 'udp', $flujoId);

echo "Transiciones obtenidas (count=".count($trans)."):\n";
foreach ($trans as $t) {
    echo "- id={$t->id}, flujo_id={$t->flujo_id}, estado_destino_id={$t->estado_destino_id}, rol_autorizado={$t->rol_autorizado}\n";
}

$ids = $trans->pluck('id')->toArray();
if (empty($ids)) {
    echo "No hay transiciones devueltas. Mostrar todas las transiciones desde estado_origen_id={$estadoOrigenId}:\n";
    $all = $db->table('tbl_flujos_estados')->where('estado_origen_id', $estadoOrigenId)->get();
    foreach ($all as $a) {
        echo "- fe.id={$a->id}, flujo_id={$a->flujo_id}, estado_destino_id={$a->estado_destino_id}, rol_autorizado={$a->rol_autorizado}\n";
    }
} else {
    echo "Pivot rows for found transitions:\n";
    $rows = $db->table('tbl_flujos_estados_roles')->whereIn('flujo_estado_id', $ids)->get();
    foreach ($rows as $r) {
        echo "- pivot id={$r->id}, flujo_estado_id={$r->flujo_estado_id}, rol_id={$r->rol_id}\n";
    }
}

// Also show matching pivot entries for flujo_estado ids 4 and 7 explicitly
echo "\nPivot rows for fe.id IN (4,7):\n";
$rows47 = $db->table('tbl_flujos_estados_roles')->whereIn('flujo_estado_id', [4,7])->get();
foreach ($rows47 as $r) echo "- pivot id={$r->id}, flujo_estado_id={$r->flujo_estado_id}, rol_id={$r->rol_id}\n";

// show user udp
$user = $db->table('tbl_personas')->whereRaw('lower(username)=?', ['udp'])->first();
if ($user) {
    echo "\nUsuario 'udp' encontrado: id={$user->id}, username={$user->username}, id_rol={$user->id_rol}\n";
} else {
    echo "\nUsuario 'udp' NO encontrado en tbl_personas\n";
}

