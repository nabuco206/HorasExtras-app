<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblBolsonHist extends Model
{
    use HasFactory;

    protected $table = 'tbl_bolson_hists';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_bolson_tiempo',
        'id_solicitud_compensa',
        'username',
        'accion',
        'minutos_afectados',
        'saldo_anterior',
        'saldo_nuevo',
        'observaciones',
    ];

    protected $casts = [
        'id_bolson_tiempo' => 'integer',
        'id_solicitud_compensa' => 'integer',
        'minutos_afectados' => 'integer',
        'saldo_anterior' => 'integer',
        'saldo_nuevo' => 'integer',
    ];

    public function bolsonTiempo(): BelongsTo
    {
        return $this->belongsTo(TblBolsonTiempo::class, 'id_bolson_tiempo');
    }

    public function solicitudCompensa(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudCompensa::class, 'id_solicitud_compensa');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(TblPersona::class, 'username', 'username');
    }

    /**
     * Scopes
     */
    public function scopePorUsuario($query, string $username)
    {
        return $query->where('username', $username);
    }

    public function scopePorTipoMovimiento($query, string $tipo)
    {
        return $query->where('accion', $tipo);
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('created_at', $fecha);
    }

    public function scopeCreditos($query)
    {
        return $query->where('accion', 'credito');
    }

    public function scopeDebitos($query)
    {
        return $query->where('accion', 'debito');
    }

    public function scopeExpiraciones($query)
    {
        return $query->where('accion', 'expiracion');
    }

    /**
     * Métodos de utilidad
     */
    public function esCredito(): bool
    {
        return $this->accion === 'credito';
    }

    public function esDebito(): bool
    {
        return $this->accion === 'debito';
    }

    public function esExpiracion(): bool
    {
        return $this->accion === 'expiracion';
    }
}
