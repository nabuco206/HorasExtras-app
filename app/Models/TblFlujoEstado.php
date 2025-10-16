<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/TblFlujoEstado.php
class TblFlujoEstado extends Model
{
    protected $table = 'tbl_flujos_estados';
    protected $fillable = ['flujo_id', 'estado_origen_id', 'estado_destino_id', 'rol_autorizado', 'condicion_sql', 'orden', 'activo'];

    public function flujo()
    {
        return $this->belongsTo(TblFlujo::class, 'flujo_id');
    }

    public function estadoOrigen()
    {
        return $this->belongsTo(TblEstado::class, 'estado_origen_id');
    }

    public function estadoDestino()
    {
        return $this->belongsTo(TblEstado::class, 'estado_destino_id');
    }
}

