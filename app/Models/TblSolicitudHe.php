<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblSolicitudHe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'tipo_trabajo',
        'fecha',
        'hrs_inicial',
        'hrs_final',
        'id_estado',
        'tipo_solicitud',
        'fecha_evento',
        'hrs_inicio',
        'hrs_fin',
        'id_tipoCompensacion',
        'min_25',
        'min_50',
        'total_min',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tipo_trabajo' => 'integer',
        'fecha' => 'date',
        'id_estado' => 'integer',
        'fecha_evento' => 'date',
        'id_tipoCompensacion' => 'integer',
        'min_25' => 'integer',
        'min_50' => 'integer',
        'total_min' => 'integer',
    ];

    public function tblSeguimientoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TblSeguimientoSolicitud::class, 'id', 'id_solicitud_he');
    }

    public function idEstado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class);
    }
}
