<?php

namespace App\Jobs;

use App\Services\MailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TblPersona;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Log;

class EnviarCorreosRolesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::warning("LLega 02");
        $mailService = app(MailService::class);
        $jefaturas = TblPersona::where('id_rol', 2)
            ->whereHas('solicitudes', function ($query) {
                $query->where('id_estado', 1);
            })
            ->get();

        // Log::warning("JEFATURA".$jefaturas);

        foreach ($jefaturas as $jefatura) {
            // Generar el correo electrónico basado en el campo 'username'
            $correoUsuario = $jefatura->username . '@minpublico.cl';
            Log::warning("JEFATURA CORREO: ".$correoUsuario);

            // if (empty($correoUsuario)) {
            //     Log::warning("La jefatura {$jefatura->nombre} no tiene un correo electrónico válido.");
            //     continue;
            // }

            $solicitudes = TblSolicitudHe::where('cod_fiscalia', $jefatura->cod_fiscalia)
                ->where('id_estado', 2)
                ->get();

                Log::warning("SOLICITUDS CORREO: ". $solicitudes);



            // $detalleSolicitudes = $solicitudes->map(function ($solicitud) {
            //     return [
            //         'ID' => $solicitud->id,
            //         'Usuario' => $solicitud->username,
            //         'Fecha' => $solicitud->created_at->format('d/m/Y'),
            //         'Estado' => $solicitud->estado->gls_estado ?? 'Desconocido',
            //     ];
            // });

            // $mailService->enviarCorreoRolesConDetalle($jefatura, $detalleSolicitudes);
        }
    }
}
