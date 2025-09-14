<?php

namespace App\Services;

use App\Models\TblSeguimientoSolicitud;

class SeguimientoSolicitudLogger
{
    /**
     * Registra un seguimiento para una solicitud HE u otro evento.
     *
     * @param int $idSolicitudHe
     * @param string $username
     * @param int $idEstado
     * @return TblSeguimientoSolicitud
     */
    public static function log(int $idSolicitudHe, string $username, int $idEstado): TblSeguimientoSolicitud
    {
        return TblSeguimientoSolicitud::create([
            'id_solicitud_he' => $idSolicitudHe,
            'username' => $username,
            'id_estado' => $idEstado,
        ]);
    }
}
