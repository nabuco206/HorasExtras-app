<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblTipoTrabajo extends Model
{
    use HasFactory;

    protected $table = 'tbl_tipo_trabajo'; // Asegúrate que el nombre coincide con tu tabla
    // Si tu tabla no tiene timestamps, agrega:
    // public $timestamps = false;
    protected $fillable = [
        'gls_tipo_trabajo',
    ];

}
