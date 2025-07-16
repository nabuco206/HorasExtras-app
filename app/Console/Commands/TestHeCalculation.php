<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SolicitudHeService;

class TestHeCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:he-calculation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el cálculo de horas extras con diferentes escenarios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== PRUEBAS DE CÁLCULO DE HORAS EXTRAS ===');
        $this->newLine();

        $service = app(SolicitudHeService::class);

        // Casos de prueba
        $casos = [
            [
                'descripcion' => 'CASO ESPECÍFICO: Lunes 18:00 a 22:00 (Debe ser 315 total)',
                'fecha' => '2025-07-14', // Lunes
                'inicio' => '18:00',
                'fin' => '22:00',
                'esperado' => [
                    'min_reales' => 240,
                    'min_25' => 45,     // 180 min * 0.25 = 45
                    'min_50' => 30,     // 60 min * 0.50 = 30
                    'total_min' => 315  // 240 + 45 + 30 = 315
                ]
            ],
            [
                'descripcion' => 'JORNADA NORMAL: Lunes 08:00 a 18:00 (sin HE)',
                'fecha' => '2025-07-14', // Lunes
                'inicio' => '08:00',
                'fin' => '18:00'
            ],
            [
                'descripcion' => 'POST JORNADA 25%: Lunes 18:00 a 20:30',
                'fecha' => '2025-07-14', // Lunes
                'inicio' => '18:00',
                'fin' => '20:30'
            ],
            [
                'descripcion' => 'MIXTO 25% y 50%: Martes 19:00 a 22:00',
                'fecha' => '2025-07-15', // Martes
                'inicio' => '19:00',
                'fin' => '22:00'
            ],
            [
                'descripcion' => 'SÁBADO 50%: Todo el día',
                'fecha' => '2025-07-19', // Sábado
                'inicio' => '10:00',
                'fin' => '14:00'
            ]
        ];

        foreach ($casos as $caso) {
            $this->warn("--- {$caso['descripcion']} ---");
            $this->line("Fecha: {$caso['fecha']}");
            $this->line("Horario: {$caso['inicio']} a {$caso['fin']}");
            
            try {
                $resultado = $service->calculaPorcentaje(
                    $caso['fecha'],
                    $caso['inicio'],
                    $caso['fin']
                );
                     $this->line("Minutos reales: {$resultado['min_reales']}");
            $this->line("Recargo 25%: {$resultado['min_25']}");
            $this->line("Recargo 50%: {$resultado['min_50']}");
            $this->line("Total minutos: {$resultado['total_min']}");
            
            // Verificar si coincide con lo esperado
            if (isset($caso['esperado'])) {
                $this->newLine();
                $this->line("ESPERADO:");
                $this->line("  Min reales: {$caso['esperado']['min_reales']}");
                $this->line("  Recargo 25%: {$caso['esperado']['min_25']}");
                $this->line("  Recargo 50%: {$caso['esperado']['min_50']}");
                $this->line("  Total: {$caso['esperado']['total_min']}");
                
                $coincide = $resultado['min_reales'] == $caso['esperado']['min_reales'] &&
                           $resultado['min_25'] == $caso['esperado']['min_25'] &&
                           $resultado['min_50'] == $caso['esperado']['min_50'] &&
                           $resultado['total_min'] == $caso['esperado']['total_min'];
                
                if ($coincide) {
                    $this->info("✅ RESULTADO CORRECTO");
                } else {
                    $this->error("❌ RESULTADO INCORRECTO");
                }
            }
                
                if (isset($resultado['detalles'])) {
                    $this->line("Detalles:");
                    foreach ($resultado['detalles'] as $detalle) {
                        $this->line("  - {$detalle['configuracion']}: {$detalle['minutos_reales']} min reales, +{$detalle['minutos_recargo']} recargo ({$detalle['porcentaje']}%)");
                    }
                }
                
            } catch (\Exception $e) {
                $this->error("ERROR: " . $e->getMessage());
            }
            
            $this->newLine();
        }

        $this->info('=== FIN DE PRUEBAS ===');
    }
}
