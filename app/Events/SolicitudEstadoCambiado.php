<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SolicitudEstadoCambiado
{
    use SerializesModels;

    public $model;
    public $estadoAnterior;
    public $estadoNuevo;
    public $solicitud;
    public $estado;

    /**
     * Create a new event instance.
     *
     * @param array $solicitud
     * @param string $estado
     */
    public function __construct(array $solicitud, string $estado)
    {
        $this->solicitud = $solicitud;
        $this->estado = $estado;
    }
}
