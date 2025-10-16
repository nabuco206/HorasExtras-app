<?php

namespace App\Console\Commands;

use App\Models\TblBolsonTiempo;
use App\Models\TblBolsonHist;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ConfigurarPruebas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bolson:test-setup
                            {username : Usuario para configurar}
                            {--reset : Limpiar datos existentes}
                            {--duration=5 : Duración en minutos para cada bolsón}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar sistema de bolsón para pruebas rápidas con duraciones cortas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $duracionMinutos = intval($this->option('duration'));

        $this->info("🧪 CONFIGURANDO MODO PRUEBA PARA: {$username}");
        $this->info("⏱️  Duración de bolsones: {$duracionMinutos} minutos");
        $this->newLine();

        if ($this->option('reset')) {
            $this->resetearDatos($username);
        }

        $this->crearBolsonesPrueba($username, $duracionMinutos);
        $this->mostrarInstrucciones($username, $duracionMinutos);
    }

    private function resetearDatos(string $username): void
    {
        $this->info("🗑️  Eliminando datos existentes...");

        TblBolsonHist::whereHas('bolsonTiempo', function($query) use ($username) {
            $query->where('username', $username);
        })->delete();

        TblBolsonTiempo::where('username', $username)->delete();

        $this->info("✅ Datos eliminados");
    }

    private function crearBolsonesPrueba(string $username, int $duracionMinutos): void
    {
        $this->info("🏗️  Creando bolsones de prueba...");

        // Obtener solicitudes HE existentes
        $solicitudesExistentes = \App\Models\TblSolicitudHe::where('username', $username)
            ->pluck('id')
            ->toArray();

        if (empty($solicitudesExistentes)) {
            $this->error("❌ No hay solicitudes HE para {$username}");
            $this->info("💡 Crea algunas usando: /sistema/ingreso-he");
            return;
        }

        $ahora = Carbon::now();

        // Crear bolsones con diferentes tiempos de vencimiento (en días para pruebas)
        $bolsones = [
            [
                'nombre' => 'Bolsón A (vence HOY)',
                'minutos' => 300,
                'vence_en_dias' => 0, // Vence hoy
                'color' => 'red'
            ],
            [
                'nombre' => 'Bolsón B (vence MAÑANA)',
                'minutos' => 180,
                'vence_en_dias' => 1, // Vence mañana
                'color' => 'yellow'
            ],
            [
                'nombre' => 'Bolsón C (vence en 2 días)',
                'minutos' => 120,
                'vence_en_dias' => 2, // Vence en 2 días
                'color' => 'green'
            ]
        ];        $this->table(
            ['Bolsón', 'Minutos', 'Vence en', 'Fecha Vencimiento'],
            collect($bolsones)->map(function($b) use ($ahora) {
                $dias = $b['vence_en_dias'] == 0 ? 'HOY' : $b['vence_en_dias'] . ' día(s)';
                return [
                    $b['nombre'],
                    $b['minutos'],
                    $dias,
                    $ahora->copy()->addDays($b['vence_en_dias'])->format('d/m/Y')
                ];
            })->all()
        );

        foreach ($bolsones as $index => $bolsonData) {
            $solicitudId = $solicitudesExistentes[$index % count($solicitudesExistentes)];

            TblBolsonTiempo::create([
                'username' => $username,
                'id_solicitud_he' => $solicitudId,
                'fecha_crea' => $ahora->copy()->subDays(3 + $index)->toDateString(),
                'minutos' => $bolsonData['minutos'],
                'fecha_vence' => $ahora->copy()->addDays($bolsonData['vence_en_dias'])->toDateString(),
                'saldo_min' => $bolsonData['minutos'],
                'origen' => 'HE_APROBADA',
                'activo' => true,
            ]);
        }

        $this->info("✅ " . count($bolsones) . " bolsones creados exitosamente");
    }

    private function mostrarInstrucciones(string $username, int $duracionMinutos): void
    {
        $this->newLine();
        $this->info("📋 INSTRUCCIONES PARA PRUEBAS:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        $this->comment("🔍 1. Ver estado actual:");
        $this->line("   php artisan bolson:simular {$username} --resumen");
        $this->newLine();

        $this->comment("🧮 2. Simular descuento (ejemplo 200 min):");
        $this->line("   php artisan bolson:simular {$username} --descuento=200");
        $this->newLine();

        $this->comment("⏰ 3. Probar expiraciones (el Bolsón A vence HOY):");
        $this->line("   php artisan bolson:expirar --dry-run");
        $this->line("   php artisan bolson:expirar --force");
        $this->newLine();

        $this->comment("🌐 4. Ver en la web:");
        $this->line("   - Dashboard: /dashboard");
        $this->line("   - Ingreso HE: /sistema/ingreso-he (ver cuadro flotante)");
        $this->newLine();

        $this->comment("🔄 5. Repetir configuración:");
        $this->line("   php artisan bolson:test-setup {$username} --reset --duration=3");
        $this->newLine();

        $this->warn("⚠️  IMPORTANTE: Bolsón A vence HOY, B mañana, C en 2 días");
        $this->info("💡 Tip: Abre otra terminal para ir monitoreando con --resumen");
    }
}
