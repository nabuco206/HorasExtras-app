<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/TblEstado.php
class TblEstado extends Model
{
    protected $table = 'tbl_estados';
    protected $fillable = ['codigo', 'descripcion', 'tipo_accion', 'flujo', 'es_final', 'activo'];

    public function transicionesOrigen()
    {
        return $this->hasMany(TblFlujoEstado::class, 'estado_origen_id');
    }

    public function transicionesDestino()
    {
        return $this->hasMany(TblFlujoEstado::class, 'estado_destino_id');
    }
}

