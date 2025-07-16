<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblTipoTrabajo extends Model
{
    use HasFactory;

    protected $table = 'tbl_tipo_trabajo';
    
    protected $fillable = [
        'gls_tipo_trabajo',
        'flag_activo',
    ];

    protected $casts = [
        'flag_activo' => 'boolean',
    ];

    /**
     * Verifica si el tipo de trabajo está activo
     *
     * @return bool
     */
    public function estaActivo(): bool
    {
        return $this->flag_activo === true;
    }

    /**
     * Scope para obtener solo los tipos de trabajo activos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('flag_activo', true);
    }
}
