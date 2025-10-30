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
        'estado',
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
                    ->where('estado', 'DISPONIBLE')
                    ->where('saldo_min', '>', 0);
    }

    /**
     * Scope para obtener bolsones pendientes de aprobación
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE')
                    ->where('activo', true);
    }

    /**
     * Verificar si el bolsón está vigente para uso
     */
    public function estaVigente(): bool
    {
        return $this->fecha_vence >= now()->toDateString()
               && $this->activo
               && $this->estado === 'DISPONIBLE'
               && $this->saldo_min > 0;
    }

    /**
     * Verificar si el bolsón está pendiente de aprobación
     */
    public function estaPendiente(): bool
    {
        return $this->estado === 'PENDIENTE' && $this->activo;
    }

    /**
     * Marcar como disponible (aprobado)
     */
    public function marcarComoDisponible(): bool
    {
        if ($this->estado === 'PENDIENTE') {
            $this->estado = 'DISPONIBLE';
            return $this->save();
        }
        return false;
    }

    /**
     * Marcar como utilizado
     */
    public function marcarComoUtilizado($minutosUtilizados = null): bool
    {
        if ($this->estado === 'DISPONIBLE') {
            $this->estado = 'UTILIZADO';
            if ($minutosUtilizados !== null) {
                $this->saldo_min = max(0, $this->saldo_min - $minutosUtilizados);
            }
            return $this->save();
        }
        return false;
    }
}
