<?php

namespace App\Console\Commands;

use App\Services\BolsonService;
use App\Models\TblBolsonTiempo;
use Illuminate\Console\Command;

class ExpirarBolsones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bolson:expirar
                            {--dry-run : Mostrar qué se expiraría sin ejecutar cambios}
                            {--force : Forzar ejecución sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expira bolsones de tiempo vencidos y actualiza saldos';

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
        $this->info('🕒 Iniciando proceso de expiración de bolsones...');

        if ($this->option('dry-run')) {
            $this->info('📋 Modo DRY-RUN: Se mostrarán los bolsones que se expirarían');
            $this->mostrarBolsonesVencidos();
            return;
        }

        if (!$this->option('force') && !$this->confirm('¿Desea continuar con la expiración de bolsones vencidos?')) {
            $this->info('❌ Operación cancelada');
            return;
        }

        try {
            $this->info('⏳ Procesando bolsones vencidos...');

            $bolsonesExpirados = $this->bolsonService->expirarBolsonesVencidos();

            if (count($bolsonesExpirados) > 0) {
                $totalMinutosExpirados = collect($bolsonesExpirados)->sum('minutos_perdidos');
                $this->info("✅ Proceso completado exitosamente");
                $this->info("📊 Bolsones expirados: " . count($bolsonesExpirados));
                $this->info("📊 Total expirado: {$totalMinutosExpirados} minutos");

                foreach ($bolsonesExpirados as $bolson) {
                    $this->line("   - ID {$bolson['bolson_id']} ({$bolson['username']}): {$bolson['minutos_perdidos']} min");
                }
            } else {
                $this->info("ℹ️  No se encontraron bolsones vencidos para expirar");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error al procesar expiración: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Mostrar bolsones que serían expirados (modo dry-run)
     */
    private function mostrarBolsonesVencidos(): void
    {
        $vencidos = TblBolsonTiempo::where('activo', true)
            ->where('saldo_min', '>', 0)
            ->where('fecha_vence', '<=', now())
            ->get();

        if ($vencidos->isEmpty()) {
            $this->info("ℹ️  No hay bolsones vencidos para expirar");
            return;
        }

        $this->info("📋 Bolsones que serían expirados:");
        $this->newLine();

        $headers = ['ID', 'Usuario', 'Minutos', 'Vencimiento', 'Días vencido'];
        $rows = [];

        foreach ($vencidos as $bolson) {
            $diasVencido = now()->diffInDays($bolson->fecha_vence);
            $rows[] = [
                $bolson->id,
                $bolson->username,
                $bolson->saldo_min,
                $bolson->fecha_vence,
                $diasVencido
            ];
        }

        $this->table($headers, $rows);

        $totalMinutos = $vencidos->sum('saldo_min');

        $this->newLine();
        $this->info("📊 Total a expirar: {$totalMinutos} minutos");
    }
}
