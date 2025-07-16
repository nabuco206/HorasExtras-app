<?php

require_once 'vendor/autoload.php';

use App\Services\SolicitudHeService;
use Illuminate\Foundation\Application;

// Configurar la aplicación Laravel básica para las pruebas
$app = new Application(realpath(__DIR__));
$app->singleton('path.config', function () {
    return __DIR__ . '/config';
});
$app->singleton('path.storage', function () {
    return __DIR__ . '/storage';
});

// Cargar configuración de base de datos
$app['config']->set('database.default', 'pgsql');
$app['config']->set('database.connections.pgsql', [
    'driver' => 'pgsql',
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'horasextras_app',
    'username' => 'postgres',
    'password' => '1234',
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
]);

// Inicializar el framework
$app->make('Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables')->bootstrap($app);
$app->make('Illuminate\Foundation\Bootstrap\LoadConfiguration')->bootstrap($app);
$app->make('Illuminate\Foundation\Bootstrap\HandleExceptions')->bootstrap($app);
$app->make('Illuminate\Foundation\Bootstrap\RegisterFacades')->bootstrap($app);
$app->make('Illuminate\Foundation\Bootstrap\RegisterProviders')->bootstrap($app);
$app->make('Illuminate\Foundation\Bootstrap\BootProviders')->bootstrap($app);

echo "=== PRUEBAS DE CÁLCULO DE HORAS EXTRAS ===\n\n";

$service = new SolicitudHeService();

// Casos de prueba
$casos = [
    [
        'descripcion' => 'Día laboral: 18:00 a 20:30 (debe ser 25%)',
        'fecha' => '2025-07-15', // Martes
        'inicio' => '18:00',
        'fin' => '20:30'
    ],
    [
        'descripcion' => 'Día laboral: 19:00 a 22:00 (18:00-20:59=25%, 21:00-22:00=50%)',
        'fecha' => '2025-07-15', // Martes
        'inicio' => '19:00',
        'fin' => '22:00'
    ],
    [
        'descripcion' => 'Fin de semana: 10:00 a 12:00 (debe ser 50%)',
        'fecha' => '2025-07-19', // Sábado
        'inicio' => '10:00',
        'fin' => '12:00'
    ],
    [
        'descripcion' => 'Día laboral nocturno: 22:00 a 02:00 (debe ser 50%)',
        'fecha' => '2025-07-15', // Martes
        'inicio' => '22:00',
        'fin' => '02:00'
    ]
];

foreach ($casos as $caso) {
    echo "--- {$caso['descripcion']} ---\n";
    echo "Fecha: {$caso['fecha']}\n";
    echo "Horario: {$caso['inicio']} a {$caso['fin']}\n";
    
    try {
        $resultado = $service->calculaPorcentaje(
            $caso['fecha'],
            $caso['inicio'],
            $caso['fin']
        );
        
        echo "Minutos reales: {$resultado['min_reales']}\n";
        echo "Recargo 25%: {$resultado['min_25']}\n";
        echo "Recargo 50%: {$resultado['min_50']}\n";
        echo "Total minutos: {$resultado['total_min']}\n";
        
        if (isset($resultado['detalles'])) {
            echo "Detalles:\n";
            foreach ($resultado['detalles'] as $detalle) {
                echo "  - {$detalle['configuracion']}: {$detalle['minutos_reales']} min reales, +{$detalle['minutos_recargo']} recargo ({$detalle['porcentaje']}%)\n";
            }
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== FIN DE PRUEBAS ===\n";
