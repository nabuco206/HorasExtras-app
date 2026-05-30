<?php

namespace App\Listeners;

use App\Events\SolicitudEstadoCambiado;
use App\Notifications\SolicitudEstadoNotificacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\TblLider;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Throwable;

class EnviarCorreoYRegistroNotificacion implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SolicitudEstadoCambiado $event)
    {
        Log::info("EnviarCorreoYRegistroNotificacion: handle .", [
            'model_class' => get_class($event->model),
            'model_id' => $event->model->id ?? null,
            'estadoAntes' => $event->estadoAnterior,
            'estadoDespues' => $event->estadoNuevo,
        ]);

        $model = $event->model;
        $estadoAnterior = $event->estadoAnterior;
        $estadoNuevo = $event->estadoNuevo;

        $codFiscalia = $model->cod_fiscalia ?? ($model->persona->cod_fiscalia ?? null);

        $emails = [];
        $emails[] ='crojasm@test.cl';

        // if ($codFiscalia) {
        //     // obtener líder y usar su email directamente (no dependemos de users table)
        //     // $lider = TblLider::with('persona')->where('cod_fiscalia', $codFiscalia)->where('flag_activo', true)->first();
        //     Log::info("EnviarCorreoYRegistroNotificacion: líder resuelto", [
        //         'cod_fiscalia' => $codFiscalia,
        //         'lider_id' => $lider->id ?? null,
        //         'lider_username' => $lider->persona->username ?? null,
        //     ]);
        //     if (!empty($lider->persona->username)) {
        //         $emails[] = $lider->persona->username.'@prueba.cl';
        //     }
        // }

        if (empty($emails)) {
            Log::warning('EnviarCorreoYRegistroNotificacion: no se resolvieron destinatarios', [
                'model_class' => get_class($model),
                'model_id' => $model->id ?? null,
                'cod_fiscalia' => $codFiscalia,
            ]);
            return;
        }

        foreach ($emails as $email) {
            try {
                Notification::route('mail', $email)
                    ->notify(new SolicitudEstadoNotificacion($model, $estadoAnterior, $estadoNuevo));

                // insertar registro manual en notifications (opcional)
                try {
                    DB::table('notifications')->insert([
                        'id' => (string) Str::uuid(),
                        'type' => 'App\\Notifications\\SolicitudEstadoNotificacion',
                        'notifiable_type' => 'email',
                        'notifiable_id' => 0,
                        'data' => json_encode([
                            'solicitud_id' => $model->id,
                            'email' => $email,
                            'estado_anterior' => $estadoAnterior,
                            'estado_nuevo' => $estadoNuevo,
                            'mensaje' => "Cambio de estado solicitud #{$model->id}"
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    Log::info('EnviarCorreoYRegistroNotificacion: inserted notification', ['email' => $email]);
                } catch (Throwable $e) {
                    Log::error('EnviarCorreoYRegistroNotificacion: DB insert failed', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'email' => $email,
                        'model_id' => $model->id ?? null,
                    ]);
                    // opcional: rethrow si quieres que el job falle y quede en failed_jobs
                    // throw $e;
                }



                Log::info('EnviarCorreoYRegistroNotificacion: mail enviado a', ['email' => $email]);
            } catch (Throwable $e) {
                Log::error('EnviarCorreoYRegistroNotificacion: error enviando mail', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info("EnviarCorreoYRegistroNotificacion: handle finished.");
    }
}
