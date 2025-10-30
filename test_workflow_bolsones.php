<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\FlujoEstadoService;
use App\Services\BolsonService;
use App\Models\TblSolicitudHe;
use App\Models\TblBolsonTiempo;

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PRUEBA DEL SISTEMA DE WORKFLOW CON BOLSONES PENDIENTES ===\n\n";

try {
    // 1. Crear una solicitud de prueba
    echo "1. Creando solicitud de prueba...\n";
    $flujoService = new FlujoEstadoService();

    // Usar un usuario existente de la tabla personas
    $usuarioExistente = \App\Models\TblPersona::first();
    if (!$usuarioExistente) {
        echo "   ❌ No hay usuarios en tbl_personas. Creando uno...\n";
        $usuarioExistente = \App\Models\TblPersona::create([
            'username' => 'test_user',
            'nombres' => 'Usuario',
            'apellidos' => 'Prueba',
            'cod_fiscalia' => 1,
            'activo' => true
        ]);
    }

    $resultado = $flujoService->crearSolicitudPrueba($usuarioExistente->username, 180);

    if ($resultado['exitoso']) {
        $solicitud = $resultado['solicitud'];
        $bolson = $resultado['bolson'];

        echo "   ✅ Solicitud creada: ID #{$solicitud->id}\n";
        echo "   ✅ Usuario: {$solicitud->username}\n";
        echo "   ✅ Total minutos: {$solicitud->total_min}\n";
        echo "   ✅ Estado inicial: {$solicitud->estado->gls_estado}\n";

        if ($bolson) {
            echo "   ✅ Bolsón creado: ID #{$bolson->id}\n";
            echo "   ✅ Estado bolsón: {$bolson->estado}\n";
            echo "   ✅ Minutos en bolsón: {$bolson->minutos}\n";
        }

        echo "\n";

        // 2. Verificar que el bolsón esté pendiente
        echo "2. Verificando estado del bolsón...\n";
        $bolsonService = new BolsonService();
        $pendientes = $bolsonService->obtenerBolsonesPendientes($solicitud->username);

        echo "   📋 Bolsones pendientes encontrados: " . count($pendientes) . "\n";
        foreach ($pendientes as $pendiente) {
            echo "   - Bolsón ID #{$pendiente['id']}: {$pendiente['minutos']} min - {$pendiente['estado']}\n";
        }

        // 3. Simular aprobación de la solicitud
        echo "\n3. Simulando aprobación por el jefe...\n";
        $estadoAprobadoJefe = 3; // APROBADO_JEFE según nuestro seeder

        $transicionResult = $flujoService->ejecutarTransicion(
            $solicitud,
            $estadoAprobadoJefe,
            1, // usuario_id simulado
            'Aprobación automática de prueba'
        );

        if ($transicionResult['exitoso']) {
            echo "   ✅ Transición exitosa: {$transicionResult['mensaje']}\n";

            // Refrescar la solicitud desde la base de datos
            $solicitud->refresh();
            echo "   ✅ Nuevo estado: {$solicitud->estado->gls_estado}\n";

            // Verificar el bolsón
            $bolson->refresh();
            echo "   ✅ Estado del bolsón: {$bolson->estado}\n";
        } else {
            echo "   ❌ Error en transición: {$transicionResult['mensaje']}\n";
        }

        // 4. Verificar bolsones disponibles
        echo "\n4. Verificando bolsones disponibles después de aprobación...\n";
        $resumen = $bolsonService->obtenerResumenCompleto($solicitud->username);

        echo "   📊 Total disponible: {$resumen['total_disponible']} min\n";
        echo "   📊 Total pendiente: {$resumen['total_pendiente']} min\n";
        echo "   📊 Bolsones disponibles: {$resumen['bolsones_disponibles']}\n";
        echo "   📊 Bolsones pendientes: {$resumen['bolsones_pendientes']}\n";

    } else {
        echo "   ❌ Error: {$resultado['mensaje']}\n";
    }

} catch (\Exception $e) {
    echo "❌ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";
