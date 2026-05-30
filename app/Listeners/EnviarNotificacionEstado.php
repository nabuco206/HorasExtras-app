<?php

namespace App\Listeners;

use App\Events\SolicitudEstadoCambiado;
use App\Services\MailService;

class EnviarNotificacionEstado
{
    protected $mailService;

    /**
     * Create the event listener.
     *
     * @param MailService $mailService
     */
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Handle the event.
     *
     * @param SolicitudEstadoCambiado $event
     * @return void
     */
    public function handle(SolicitudEstadoCambiado $event)
    {
        $solicitud = $event->solicitud;
        $estado = $event->estado;

        switch ($estado) {
            case 'INGRESADO':
                $this->mailService->sendSolicitudIngresada($solicitud);
                break;
            case 'APROBADO_JD_D':
            case 'APROBADO_UDP_D':
            case 'APROBADO_JUDP_D':
            case 'APROBADO_DER_D':
            case 'APROBADO_JEFE':
                $this->mailService->sendAprobacion($solicitud, $solicitud['aprobador_email']);
                break;
            case 'RECHAZADO_JEFE':
            case 'RECHAZADO_RRHH':
                $this->mailService->sendRechazo($solicitud, $solicitud['rechazador_email']);
                break;
            case 'COMPENSACION_SOLICITADA':
            case 'COMPENSACION_APROBADA_JEFE':
            case 'COMPENSACION_RECHAZADA_JEFE':
                $this->mailService->sendCompensacion($solicitud, $estado);
                break;
        }
    }
}
