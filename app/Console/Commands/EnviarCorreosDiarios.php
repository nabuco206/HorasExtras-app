<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\EnviarCorreosRolesJob;

class EnviarCorreosDiarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enviar:correos-diarios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar correos diarios a roles con solicitudes pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Despachar el Job para enviar correos
        EnviarCorreosRolesJob::dispatch();

        $this->info('Correos diarios enviados correctamente.');
    }
}
