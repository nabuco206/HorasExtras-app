<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblLider extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'persona_id',
        'cod_fiscalia',
        'gls_unidad',
        'flag_activo',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cod_fiscalia' => 'integer',
        'persona_id' => 'integer',
        'flag_activo' => 'boolean',
    ];

    /**
     * Relación con TblFiscalia
     */
    public function fiscalia(): BelongsTo
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'cod_fiscalia');
    }

    /**
     * Relación con TblPersona
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(TblPersona::class, 'persona_id', 'id');
    }

    /**
     * Verifica si el líder está activo
     *
     * @return bool
     */
    public function estaActivo(): bool
    {
        return $this->flag_activo === true;
    }

    /**
     * Scope para obtener solo los líderes activos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('flag_activo', true);
    }
}
