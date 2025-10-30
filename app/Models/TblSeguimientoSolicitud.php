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
        'estado_anterior_id',
        'estado_nuevo_id',
        'usuario_id',
        'observaciones',
        'fecha_cambio',
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
        'estado_anterior_id' => 'integer',
        'estado_nuevo_id' => 'integer',
        'usuario_id' => 'integer',
        'fecha_cambio' => 'datetime',
    ];

    public function solicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'id_solicitud_he');
    }

    public function idEstado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'id_estado');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'id_estado');
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'estado_anterior_id');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'estado_nuevo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Obtener el nombre del usuario que realizó el cambio
     */
    public function getNombreUsuarioAttribute(): ?string
    {
        return $this->usuario ? $this->usuario->name : $this->username;
    }

    /**
     * Obtener descripción del cambio de estado
     */
    public function getDescripcionCambioAttribute(): string
    {
        $estadoAnterior = $this->estadoAnterior ? $this->estadoAnterior->gls_estado : 'Inicio';
        $estadoNuevo = $this->estadoNuevo ? $this->estadoNuevo->gls_estado : 'Desconocido';

        return "De '{$estadoAnterior}' a '{$estadoNuevo}'";
    }
}
