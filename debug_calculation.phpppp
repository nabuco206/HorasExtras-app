<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Services\SolicitudHeService;

echo "=== DEBUG DETALLADO ===\n";

$service = new SolicitudHeService();

// Probar caso específico: 18:00 a 22:00
$fecha = '2025-07-14';
$inicio = '18:00';
$fin = '22:00';

echo "Caso: $fecha de $inicio a $fin\n";

// Calcular manualmente
$inicioMin = 18 * 60; // 1080
$finMin = 22 * 60;    // 1320
$totalMin = $finMin - $inicioMin; // 240

echo "Inicio: $inicioMin min (18:00)\n";
echo "Fin: $finMin min (22:00)\n";
echo "Total: $totalMin min\n\n";

// Rangos esperados:
echo "ESPERADO:\n";
echo "18:00-21:00 (25%): " . (21*60 - 18*60) . " min reales + " . ((21*60 - 18*60) * 0.25) . " recargo\n";
echo "21:00-22:00 (50%): " . (22*60 - 21*60) . " min reales + " . ((22*60 - 21*60) * 0.50) . " recargo\n";
echo "TOTAL: " . (240 + 45 + 30) . " min\n\n";

try {
    $resultado = $service->calculaPorcentaje($fecha, $inicio, $fin);
    echo "RESULTADO ACTUAL:\n";
    echo "Min reales: " . $resultado['min_reales'] . "\n";
    echo "Recargo 25%: " . $resultado['min_25'] . "\n";
    echo "Recargo 50%: " . $resultado['min_50'] . "\n";
    echo "Total: " . $resultado['total_min'] . "\n\n";
    
    if (isset($resultado['detalles'])) {
        echo "DETALLES:\n";
        foreach ($resultado['detalles'] as $detalle) {
            echo "- {$detalle['configuracion']}: {$detalle['minutos_reales']} min reales, +{$detalle['minutos_recargo']} recargo ({$detalle['porcentaje']}%)\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
