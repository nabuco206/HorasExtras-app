<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblBolsonTiempo extends Model
{
    use HasFactory;

    protected $table = 'tbl_bolson_tiempos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'id_solicitud',
        'minutos_agregados',
        'saldo_minutos',
        'fec_creacion',
        'fec_vence',
        'origen',
        'id_estado',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_solicitud' => 'integer',
        'minutos_agregados' => 'integer',
        'saldo_minutos' => 'integer',
    ];

    public $timestamps = true;

    public function idSolicitud(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class);
    }
}
