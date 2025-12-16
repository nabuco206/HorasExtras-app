<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CREANDO HE DE PRUEBA ===\n\n";

use App\Models\TblSolicitudHe;
use App\Models\TblTipoTrabajo;

// Obtener tipos de trabajo disponibles
$tipoTrabajo = TblTipoTrabajo::first();
if (!$tipoTrabajo) {
    echo "No hay tipos de trabajo disponibles\n";
    exit;
}

// Crear 3 solicitudes de prueba
for ($i = 1; $i <= 3; $i++) {
    $he = TblSolicitudHe::create([
        'username' => 'usuario_test_' . $i,
        'cod_fiscalia' => 1,
        'id_tipo_trabajo' => $tipoTrabajo->id,
        'fecha' => now()->format('Y-m-d'),
        'hrs_inicial' => '08:00:00',
        'hrs_final' => '10:00:00',
        'id_estado' => 1, // INGRESADO
        'id_tipo_compensacion' => 1, // HE_COMPENSACION
        'min_reales' => 120,
        'min_25' => 0,
        'min_50' => 0,
        'total_min' => 120,
    ]);
    echo "HE creada: #{$he->id} para {$he->username} con {$he->total_min} minutos\n";
}

echo "\n=== HE CREADAS EXITOSAMENTE ===\n";
