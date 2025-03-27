<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblSeguimientoSolicitud extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_solicitud_he',
        'username',
        'id_estado',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_solicitud_he' => 'integer',
        'id_estado' => 'integer',
    ];

    public function idEstado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class);
    }
}
