<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblTipoEstado extends Model
{
    protected $table = 'tbl_tipo_estados';

       protected $fillable = [
        'gls_tipo_estado'
    ];
}
