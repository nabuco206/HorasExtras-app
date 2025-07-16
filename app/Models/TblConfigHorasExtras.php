<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblConfigHorasExtras extends Model
{
    use HasFactory;

    protected $table = 'tbl_config_horas_extras';

    protected $fillable = [
        'clave',
        'descripcion',
        'hora_inicio',
        'hora_fin',
        'porcentaje',
        'dias_semana',
        'aplica_feriados',
        'aplica_fines_semana',
        'activo',
        'orden'
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'porcentaje' => 'decimal:2',
        'dias_semana' => 'array',
        'aplica_feriados' => 'boolean',
        'aplica_fines_semana' => 'boolean',
        'activo' => 'boolean'
    ];

    /**
     * Scope para obtener solo configuraciones activas
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener configuraciones ordenadas
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc');
    }

    /**
     * Verificar si la configuración aplica para un día específico
     */
    public function aplicaParaDia(int $diaSemana, bool $esFeriado = false): bool
    {
        // Si es feriado y esta configuración aplica para feriados
        if ($esFeriado && $this->aplica_feriados) {
            return true;
        }

        // Si aplica para días específicos de la semana (ya no usamos aplica_fines_semana genérico)
        if ($this->dias_semana && in_array($diaSemana, $this->dias_semana)) {
            return true;
        }

        return false;
    }
}
