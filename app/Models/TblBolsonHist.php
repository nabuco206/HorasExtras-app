<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblBolsonHist extends Model
{
    use HasFactory;

    protected $table = 'tbl_bolson_hists';

    protected $fillable = [
        'id_bolson',
        'username',
        'accion',
        'minutos_afectados',
    ];

    public $timestamps = true;
}