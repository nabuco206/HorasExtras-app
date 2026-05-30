<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;


class MailService
{
    /**
     * Enviar un correo electrónico.
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return bool
     */
    public function sendEmail(string $to, string $subject, string $view, array $data = []): bool
    {
        try {
            if (empty($to)) {
                Log::error("El destinatario no tiene un correo electrónico válido.");
                return false;
            }

            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("Correo enviado a {$to} con asunto: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error al enviar correo a {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo cuando se ingresa una solicitud.
     *
     * @param array $solicitud
     * @return bool
     */
    public function sendSolicitudIngresada(array $solicitud): bool
    {
        $to = $solicitud['usuario_email'];
        $subject = "Solicitud Ingresada: {$solicitud['id']}";
        $view = 'emails.solicitud_ingresada';
        $data = ['solicitud' => $solicitud];



        return $this->sendEmail($to, $subject, $view, $data);
    }

    /**
     * Enviar correo cuando se aprueba una solicitud.
     *
     * @param array $solicitud
     * @param string $aprobadorEmail
     * @return bool
     */
    public function sendAprobacion(array $solicitud, string $aprobadorEmail): bool
    {
        $to = $solicitud['usuario_email'];
        $subject = "Solicitud Aprobada: {$solicitud['id']}";
        $view = 'emails.solicitud_aprobada';
        $data = ['solicitud' => $solicitud, 'aprobador' => $aprobadorEmail];

        return $this->sendEmail($to, $subject, $view, $data);
    }

    /**
     * Enviar correo cuando se rechaza una solicitud.
     *
     * @param array $solicitud
     * @param string $rechazadorEmail
     * @return bool
     */
    public function sendRechazo(array $solicitud, string $rechazadorEmail): bool
    {
        $to = $solicitud['usuario_email'];
        $subject = "Solicitud Rechazada: {$solicitud['id']}";
        $view = 'emails.solicitud_rechazada';
        $data = ['solicitud' => $solicitud, 'rechazador' => $rechazadorEmail];

        return $this->sendEmail($to, $subject, $view, $data);
    }

    /**
     * Enviar correo para notificar sobre una compensación.
     *
     * @param array $solicitud
     * @param string $estado
     * @return bool
     */
    public function sendCompensacion(array $solicitud, string $estado): bool
    {
        $to = $solicitud['usuario_email'];
        $subject = "Estado de Compensación: {$estado}";
        $view = 'emails.solicitud_compensacion';
        $data = ['solicitud' => $solicitud, 'estado' => $estado];

        return $this->sendEmail($to, $subject, $view, $data);
    }

    /**
     * Enviar correo con detalle de solicitudes pendientes a roles específicos.
     *
     * @param \App\Models\TblPersona $jefatura
     * @param array $detalleSolicitudes
     * @return bool
     */
    public function enviarCorreoRolesConDetalle($jefatura, $detalleSolicitudes): bool
    {
        $to = $jefatura->email; // Asegúrate de que el modelo TblPersona tiene un campo 'email'.
        $subject = "Notificación de Solicitudes Pendientes";
        $view = 'emails.roles';
        $data = [
            'jefatura' => $jefatura,
            'detalleSolicitudes' => $detalleSolicitudes,
        ];

        return $this->sendEmail($to, $subject, $view, $data);
    }
}
