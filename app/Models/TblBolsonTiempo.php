<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblBolsonTiempo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_solicitud',
        'tiempo',
        'estado',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_solicitud' => 'integer',
        'tiempo' => 'integer',
    ];

    public function idSolicitud(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class);
    }
}
