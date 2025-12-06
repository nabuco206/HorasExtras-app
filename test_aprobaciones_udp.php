<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$flujoService = new \App\Services\FlujoEstadoService();

// Obtener una HE pendiente
$solicitudesPrueba = \App\Models\TblSolicitudHe::where('id_estado', 1)
    ->latest()
    ->take(1)
    ->pluck('id')
    ->toArray();

if (empty($solicitudesPrueba)) {
    echo "No hay solicitudes pendientes para probar.\n";
    exit(0);
}

echo "=== PRUEBA APROBACIÓN CON USUARIO 'udp' ===\n";
echo "HE a aprobar: " . implode(', ', $solicitudesPrueba) . "\n\n";

$resultado = $flujoService->ejecutarTransicionesMultiples(
    $solicitudesPrueba,
    null, // dejar que el servicio determine el destino
    'udp', // pasar username 'udp' como usuarioId param
    'Prueba aprobacion con usuario udp'
);

echo "RESULTADO:\n";
echo "  - Exitoso: " . ($resultado['exitoso'] ? 'SÍ' : 'NO') . "\n";
echo "  - Procesadas: " . ($resultado['procesadas'] ?? 0) . "\n";
echo "  - Mensaje: " . ($resultado['mensaje'] ?? '') . "\n";

if (!empty($resultado['errores'])) {
    echo "ERRORES:\n";
    print_r($resultado['errores']);
}

echo "\nPRUEBA COMPLETADA\n";
