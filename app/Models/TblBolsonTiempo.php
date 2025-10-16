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
        'id_solicitud_he',
        'fecha_crea',
        'minutos',
        'fecha_vence',
        'saldo_min',
        'origen',
        'activo',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_solicitud_he' => 'integer',
        'minutos' => 'integer',
        'saldo_min' => 'integer',
        'fecha_crea' => 'date',
        'fecha_vence' => 'date',
        'activo' => 'boolean',
    ];

    public $timestamps = true;

    public function solicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'id_solicitud_he');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(TblPersona::class, 'username', 'username');
    }

    public function historial()
    {
        return $this->hasMany(TblBolsonHist::class, 'id_bolson_tiempo');
    }

    /**
     * Scope para obtener solo bolsones vigentes
     */
    public function scopeVigentes($query)
    {
        return $query->where('fecha_vence', '>=', now()->toDateString())
                    ->where('activo', true)
                    ->where('saldo_min', '>', 0);
    }

    /**
     * Verificar si el bolsón está vigente
     */
    public function estaVigente(): bool
    {
        return $this->fecha_vence >= now()->toDateString()
               && $this->activo
               && $this->saldo_min > 0;
    }
}
