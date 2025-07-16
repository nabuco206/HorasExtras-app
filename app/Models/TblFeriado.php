<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblFeriado extends Model
{
    use HasFactory;

    protected $table = 'tbl_feriados';
    
    protected $fillable = [
        'fecha', 
        'descripcion', 
        'flag_activo'
    ];

    protected $casts = [
        'flag_activo' => 'boolean',
    ];

    /**
     * Verifica si el feriado está activo
     *
     * @return bool
     */
    public function estaActivo(): bool
    {
        return $this->flag_activo === true;
    }

    /**
     * Scope para obtener solo los feriados activos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('flag_activo', true);
    }

    /**
     * Obtiene la fecha formateada para mostrar
     *
     * @return string
     */
    public function getFechaFormateadaAttribute(): string
    {
        if (!$this->fecha) return '';
        
        $partes = explode('-', $this->fecha);
        if (count($partes) == 2) {
            return $partes[0] . '/' . $partes[1]; // MM/DD
        }
        
        return $this->fecha;
    }

    /**
     * Verifica si una fecha dada coincide con este feriado
     *
     * @param string $fecha Fecha en formato Y-m-d
     * @return bool
     */
    public function esFechaDeFeriado($fecha): bool
    {
        $fechaObj = \Carbon\Carbon::parse($fecha);
        $mesdia = $fechaObj->format('m-d');
        
        return $this->fecha === $mesdia && $this->estaActivo();
    }
}
