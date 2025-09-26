<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblSolicitudCompensa extends Model
{
    protected $table = 'tbl_solicitud_compensas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'username',
        'cod_fiscalia',
        'fecha',
        'hrs_inicial',
        'hrs_final',
        'id_estado',
        'total_min',
    ];

    // Relación con fiscalías (opcional)
    public function fiscalia()
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'cod_fiscalia');
    }
}
