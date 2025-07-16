<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblSolicitudHe extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_solicitud_hes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'id_tipo_trabajo',
        'fecha',
        'hrs_inicial',
        'hrs_final',
        'id_estado',
        'tipo_solicitud',
        'fecha_evento',
        'hrs_inicio',
        'hrs_fin',
        'id_tipoCompensacion',
        'min_reales',
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
        'min_reales' => 'decimal:2',
        'min_25' => 'decimal:2',
        'min_50' => 'decimal:2',
        'total_min' => 'decimal:2',
    ];

    public function tblSeguimientoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TblSeguimientoSolicitud::class, 'id', 'id_solicitud_he');
    }

    public function idEstado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'id_estado');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'name');
    }

    public function tipoTrabajo(): BelongsTo
    {
        return $this->belongsTo(TblTipoTrabajo::class, 'id_tipo_trabajo');
    }

    public function tipoCompensacion(): BelongsTo
    {
        return $this->belongsTo(TblTipoCompensacion::class, 'id_tipoCompensacion');
    }

    /**
     * Debug method to check if username is accessible
     */
    public function hasUsername(): bool
    {
        return isset($this->attributes['username']);
    }

    /**
     * Get username safely
     */
    public function getUsername(): ?string
    {
        return $this->attributes['username'] ?? null;
    }
}
