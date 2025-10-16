<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TblSolicitudCompensa extends Model
{
    protected $table = 'tbl_solicitud_compensas';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'username',
        'cod_fiscalia',
        'fecha_solicitud',
        'hrs_inicial',
        'hrs_final',
        'minutos_solicitados',
        'minutos_aprobados',
        'id_estado',
        'observaciones',
        'aprobado_por',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'hrs_inicial' => 'datetime:H:i',
        'hrs_final' => 'datetime:H:i',
        'minutos_solicitados' => 'integer',
        'minutos_aprobados' => 'integer',
        'id_estado' => 'integer',
        'fecha_aprobacion' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(TblPersona::class, 'username', 'username');
    }

    public function fiscalia(): BelongsTo
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'cod_fiscalia');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'id_estado');
    }

    public function historialBolson(): HasMany
    {
        return $this->hasMany(TblBolsonHist::class, 'id_solicitud_compensa');
    }

    /**
     * Scopes
     */
    public function scopePendientes($query)
    {
        return $query->where('id_estado', 1);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('id_estado', 2);
    }

    public function scopeRechazadas($query)
    {
        return $query->where('id_estado', 3);
    }
}
