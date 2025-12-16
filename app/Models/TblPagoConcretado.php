<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblPagoConcretado extends Model
{
    protected $table = 'tbl_pagos_concretados';

    protected $fillable = [
        'sociedad_id',
        'fecha_pago',
        'id_empleado',
        'rut',
        'nombre',
        'sobretiempo_normal_25',
        'moneda_id',
        'sobretiempo_especial_50',
    ];
}
