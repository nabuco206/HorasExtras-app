<?php

namespace App\Console\Commands;

use App\Models\TblBolsonTiempo;
use App\Services\BolsonService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimularBolson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bolson:simular
                            {username : Usuario para la simulación}
                            {--setup : Crear datos de ejemplo}
                            {--descuento= : Simular descuento de minutos}
                            {--resumen : Mostrar resumen detallado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simular y probar la lógica FIFO del bolsón de tiempo';

    private BolsonService $bolsonService;

    public function __construct(BolsonService $bolsonService)
    {
        parent::__construct();
        $this->bolsonService = $bolsonService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');

        if ($this->option('setup')) {
            $this->crearDatosEjemplo($username);
            return;
        }

        if ($this->option('descuento')) {
            $minutos = intval($this->option('descuento'));
            $this->simularDescuento($username, $minutos);
            return;
        }

        if ($this->option('resumen')) {
            $this->mostrarResumen($username);
            return;
        }

        $this->mostrarAyuda();
    }

    private function crearDatosEjemplo(string $username): void
    {
        $this->info("🔧 Creando datos de ejemplo para el usuario: {$username}");

        // Limpiar datos existentes
        TblBolsonTiempo::where('username', $username)->delete();

        // Obtener solicitudes HE existentes para este usuario
        $solicitudesExistentes = \App\Models\TblSolicitudHe::where('username', $username)
            ->pluck('id')
            ->toArray();

        if (empty($solicitudesExistentes)) {
            $this->error("❌ No hay solicitudes HE para el usuario {$username}");
            $this->info("💡 Crea algunas solicitudes HE primero usando /sistema/ingreso-he");
            return;
        }

        // Crear bolsones de ejemplo con fechas futuras para que estén vigentes
        $hoy = Carbon::now();
        $bolsones = [
            [
                'fecha_crea' => $hoy->copy()->addDays(-10)->toDateString(),
                'minutos' => 300,
                'fecha_vence' => $hoy->copy()->addDays(2)->toDateString(), // Vence en 2 días
                'saldo_min' => 300
            ],
            [
                'fecha_crea' => $hoy->copy()->addDays(-9)->toDateString(),
                'minutos' => 150,
                'fecha_vence' => $hoy->copy()->addDays(3)->toDateString(), // Vence en 3 días
                'saldo_min' => 150
            ],
            [
                'fecha_crea' => $hoy->copy()->addDays(-8)->toDateString(),
                'minutos' => 100,
                'fecha_vence' => $hoy->copy()->addDays(4)->toDateString(), // Vence en 4 días
                'saldo_min' => 100
            ]
        ];

        foreach ($bolsones as $index => $data) {
            // Usar IDs reales de solicitudes HE
            $solicitudId = $solicitudesExistentes[$index % count($solicitudesExistentes)];

            TblBolsonTiempo::create([
                'username' => $username,
                'id_solicitud_he' => $solicitudId,
                'fecha_crea' => $data['fecha_crea'],
                'minutos' => $data['minutos'],
                'fecha_vence' => $data['fecha_vence'],
                'saldo_min' => $data['saldo_min'],
                'origen' => 'HE_APROBADA',
                'activo' => true,
            ]);
        }

        $this->info("✅ Datos de ejemplo creados exitosamente");
        $this->mostrarResumen($username);
    }

    private function simularDescuento(string $username, int $minutos): void
    {
        $this->info("🧮 Simulando descuento de {$minutos} minutos para {$username}");
        $this->newLine();

        $resultado = $this->bolsonService->simularDescuento($username, $minutos);

        $this->info("📊 RESULTADO DE LA SIMULACIÓN:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $this->table(
            ['Detalle', 'Valor'],
            [
                ['Minutos solicitados', $resultado['minutos_solicitados']],
                ['Saldo inicial', $resultado['saldo_inicial'] . ' min'],
                ['Saldo final', $resultado['saldo_final'] . ' min'],
                ['¿Suficiente saldo?', $resultado['suficiente'] ? '✅ Sí' : '❌ No'],
                ['Faltante', $resultado['faltante'] . ' min']
            ]
        );

        if (!empty($resultado['movimientos'])) {
            $this->newLine();
            $this->info("🔄 MOVIMIENTOS APLICADOS (FIFO):");

            $headers = ['Bolsón', 'HE #', 'Creación', 'Vencimiento', 'Saldo Anterior', 'Descontado', 'Saldo Nuevo', 'Estado'];
            $rows = [];

            foreach ($resultado['movimientos'] as $mov) {
                $rows[] = [
                    $mov['bolson_id'],
                    $mov['solicitud_he_id'],
                    Carbon::parse($mov['fecha_creacion'])->format('d/m'),
                    Carbon::parse($mov['fecha_vencimiento'])->format('d/m'),
                    $mov['saldo_anterior'] . ' min',
                    $mov['minutos_descontados'] . ' min',
                    $mov['saldo_nuevo'] . ' min',
                    $mov['activo_despues'] ? '✅ Activo' : '❌ Agotado'
                ];
            }

            $this->table($headers, $rows);
        }
    }

    private function mostrarResumen(string $username): void
    {
        $resumen = $this->bolsonService->obtenerResumenDetallado($username);

        $this->info("📋 RESUMEN DEL BOLSÓN - Usuario: {$username}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $this->table(
            ['Métricas', 'Valor'],
            [
                ['Total bolsones', $resumen['total_bolsones']],
                ['Bolsones vigentes', $resumen['bolsones_vigentes']],
                ['Próximos a vencer (30d)', $resumen['bolsones_proximos_vencer']],
                ['Total minutos disponibles', $resumen['total_minutos'] . ' min']
            ]
        );

        if (!empty($resumen['detalle'])) {
            $this->newLine();
            $this->info("📝 DETALLE POR BOLSÓN:");

            $headers = ['ID', 'HE #', 'Creación', 'Vencimiento', 'Min. Iniciales', 'Saldo Actual', 'Días Restantes', 'Estado'];
            $rows = [];

            foreach ($resumen['detalle'] as $bolson) {
                $estado = $bolson['vigente'] ? '✅ Vigente' : '❌ Vencido';
                if ($bolson['proximo_vencer']) {
                    $estado = '⚠️ Por vencer';
                }

                $rows[] = [
                    $bolson['id'],
                    $bolson['solicitud_he_id'],
                    Carbon::parse($bolson['fecha_creacion'])->format('d/m/Y'),
                    Carbon::parse($bolson['fecha_vencimiento'])->format('d/m/Y'),
                    $bolson['minutos_iniciales'] . ' min',
                    $bolson['saldo_actual'] . ' min',
                    $bolson['dias_restantes'],
                    $estado
                ];
            }

            $this->table($headers, $rows);
        }
    }

    private function mostrarAyuda(): void
    {
        $this->info("🔧 COMANDO SIMULADOR DEL BOLSÓN");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        $this->comment("Ejemplos de uso:");
        $this->line("• Crear datos de ejemplo:");
        $this->line("  php artisan bolson:simular persona01 --setup");
        $this->newLine();

        $this->line("• Simular descuento de 400 minutos:");
        $this->line("  php artisan bolson:simular persona01 --descuento=400");
        $this->newLine();

        $this->line("• Ver resumen del bolsón:");
        $this->line("  php artisan bolson:simular persona01 --resumen");
    }
}
