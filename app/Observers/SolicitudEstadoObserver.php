<?php

namespace App\Observers;

use App\Events\SolicitudEstadoCambiado;
use Illuminate\Support\Facades\Log;

class SolicitudEstadoObserver
{
    public function created($model)
    {
        Log::info('SolicitudEstadoObserver: CREATE event triggered.-->'.$model->cod_fiscalia);
        // al crear una solicitud, disparar evento indicando estado anterior = 0 (o null) y estado nuevo = id_estado actual
        $estadoNuevo = isset($model->id_estado) ? (int) $model->id_estado : 0;
        Log::info("SolicitudEstadoObserver: CREATE event triggered. Estado anterior: 0, Estado nuevo: {$estadoNuevo}");
        event(new SolicitudEstadoCambiado($model->toArray(), 0, $estadoNuevo));
    }

    public function updated($model)
    {
        Log::info('SolicitudEstadoObserver: UPDATE event triggered.');
        if (! isset($model->id_estado)) {
            return;
        }

        $original = $model->getOriginal('id_estado');
        $current  = $model->id_estado;

        if ((int)$original === (int)$current) {
            return;
        }

        event(new SolicitudEstadoCambiado($model->toArray(), (int)$original, (int)$current));
    }
}
