<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;

class SolicitudEstadoNotificacion extends Notification implements ShouldQueue
{
    use Queueable;

    protected $model;
    protected $estadoAnterior;
    protected $estadoNuevo;

    public function __construct($model, int $estadoAnterior, int $estadoNuevo)
    {
        Log::info("SolicitudEstadoNotificacion: __construct called for solicitud #{$model->id}");

        $this->model = $model;
        $this->estadoAnterior = $estadoAnterior;
        $this->estadoNuevo = $estadoNuevo;
    }

    public function via($notifiable)
    {
        // Si es AnonymousNotifiable (Notification::route), no usar database
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $usuario = $this->model->username ?? optional($this->model->persona)->nombre ?? ($notifiable->name ?? ($notifiable->email ?? 'Usuario'));
        $titulo = "Cambio de estado: solicitud #{$this->model->id}";

        return (new MailMessage)
            ->subject($titulo)
            ->greeting("Estimado/a")
            ->line("La solicitud #{$this->model->id} de {$usuario} ha cambiado de estado.")
            ->line("Antes: {$this->estadoAnterior}")
            ->line("Ahora: {$this->estadoNuevo}")
            ->action('Ver solicitud', url("/sistema/solicitudes/{$this->model->id}"))
            ->line('Sistema HorasExtras');
    }

    public function toDatabase($notifiable)
    {
        // calcular usuario de forma segura
        // $usuario = $this->model->username ?? optional($this->model->persona)->nombre ?? ($notifiable->name ?? null);
        $usuario ='crojasm';
        return [
            'solicitud_id' => $this->model->id,
            'username' =>  $usuario,
            'estado_anterior' => $this->estadoAnterior,
            'estado_nuevo' => $this->estadoNuevo,
            'mensaje' => "Cambio de estado solicitud #{$this->model->id}"
        ];
    }
}
