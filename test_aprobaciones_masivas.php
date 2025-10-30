<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$flujoService = new \App\Services\FlujoEstadoService();

// Obtener las últimas HE pendientes
$solicitudesPrueba = \App\Models\TblSolicitudHe::where('id_estado', 1)
    ->latest()
    ->take(3)
    ->pluck('id')
    ->toArray();

echo "=== PRUEBA DE APROBACIONES MASIVAS ===\n";
echo "HE a aprobar: " . implode(', ', $solicitudesPrueba) . "\n\n";

// Estado antes
$pendientesAntes = \App\Models\TblSolicitudHe::where('id_estado', 1)->count();
$aprobadasAntes = \App\Models\TblSolicitudHe::where('id_estado', 3)->count();
$bolsonesPendientesAntes = \App\Models\TblBolsonTiempo::where('estado', 'PENDIENTE')->count();
$bolsonesDisponiblesAntes = \App\Models\TblBolsonTiempo::where('estado', 'DISPONIBLE')->count();

echo "ANTES:\n";
echo "  - HE pendientes: $pendientesAntes\n";
echo "  - HE aprobadas: $aprobadasAntes\n";
echo "  - Bolsones PENDIENTES: $bolsonesPendientesAntes\n";
echo "  - Bolsones DISPONIBLES: $bolsonesDisponiblesAntes\n\n";

// Ejecutar aprobación masiva
$resultado = $flujoService->ejecutarTransicionesMultiples(
    $solicitudesPrueba,
    3, // APROBADO_JEFE
    1, // Usuario ID
    'Prueba de aprobación masiva automática'
);

echo "RESULTADO DE APROBACIÓN MASIVA:\n";
echo "✅ Exitoso: " . ($resultado['exitoso'] ? 'SÍ' : 'NO') . "\n";
echo "📋 Procesadas: " . $resultado['procesadas'] . "\n";
echo "🎯 Bolsones creados: " . count($resultado['bolsones_creados']) . "\n";
echo "💬 Mensaje: " . $resultado['mensaje'] . "\n";

if (!empty($resultado['bolsones_creados'])) {
    echo "\nDETALLE DE BOLSONES CREADOS:\n";
    $totalMinutos = 0;
    foreach ($resultado['bolsones_creados'] as $bolson) {
        echo "  - Bolsón #{$bolson['bolson_id']}: {$bolson['minutos']} min (HE #{$bolson['solicitud_id']} - {$bolson['username']})\n";
        $totalMinutos += $bolson['minutos'];
    }
    echo "  TOTAL: $totalMinutos minutos (" . number_format($totalMinutos/60, 1) . " horas)\n";
}

// Estado después
$pendientesDespues = \App\Models\TblSolicitudHe::where('id_estado', 1)->count();
$aprobadasDespues = \App\Models\TblSolicitudHe::where('id_estado', 3)->count();
$bolsonesPendientesDespues = \App\Models\TblBolsonTiempo::where('estado', 'PENDIENTE')->count();
$bolsonesDisponiblesDespues = \App\Models\TblBolsonTiempo::where('estado', 'DISPONIBLE')->count();

echo "\nDESPUÉS:\n";
echo "  - HE pendientes: $pendientesDespues (cambio: " . ($pendientesDespues - $pendientesAntes) . ")\n";
echo "  - HE aprobadas: $aprobadasDespues (cambio: +" . ($aprobadasDespues - $aprobadasAntes) . ")\n";
echo "  - Bolsones PENDIENTES: $bolsonesPendientesDespues (cambio: " . ($bolsonesPendientesDespues - $bolsonesPendientesAntes) . ")\n";
echo "  - Bolsones DISPONIBLES: $bolsonesDisponiblesDespues (cambio: +" . ($bolsonesDisponiblesDespues - $bolsonesDisponiblesAntes) . ")\n";

echo "\n🎉 PRUEBA DE APROBACIONES MASIVAS COMPLETADA\n";
