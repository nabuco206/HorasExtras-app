<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblFlujo extends Model
{
    protected $table = 'tbl_flujos';
    protected $fillable = ['codigo', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con flujos_estados
    public function flujosEstados()
    {
        return $this->hasMany(TblFlujoEstado::class, 'flujo_id');
    }

    // Obtener las transiciones posibles desde un estado específico
    public function transicionesDesde($estadoOrigenId)
    {
        return $this->flujosEstados()
            ->where('estado_origen_id', $estadoOrigenId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    // Obtener el próximo estado posible para un rol específico
    public function siguienteEstado($estadoOrigenId, $rol = null)
    {
        $query = $this->flujosEstados()
            ->where('estado_origen_id', $estadoOrigenId)
            ->where('activo', true);

        if ($rol) {
            $query->where(function($q) use ($rol) {
                $q->where('rol_autorizado', $rol)
                  ->orWhereNull('rol_autorizado');
            });
        }

        return $query->orderBy('orden')->first();
    }

    // Verificar si una transición es válida
    public function puedeTransicionar($estadoOrigenId, $estadoDestinoId, $rol = null)
    {
        $query = $this->flujosEstados()
            ->where('estado_origen_id', $estadoOrigenId)
            ->where('estado_destino_id', $estadoDestinoId)
            ->where('activo', true);

        if ($rol) {
            $query->where(function($q) use ($rol) {
                $q->where('rol_autorizado', $rol)
                  ->orWhereNull('rol_autorizado');
            });
        }

        return $query->exists();
    }

    // Obtener todos los estados iniciales del flujo
    public function estadosIniciales()
    {
        return $this->flujosEstados()
            ->whereNotIn('estado_origen_id', function($query) {
                $query->select('estado_destino_id')
                      ->from('tbl_flujos_estados')
                      ->where('flujo_id', $this->id);
            })
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    // Obtener todos los estados finales del flujo
    public function estadosFinales()
    {
        return $this->flujosEstados()
            ->whereNotIn('estado_destino_id', function($query) {
                $query->select('estado_origen_id')
                      ->from('tbl_flujos_estados')
                      ->where('flujo_id', $this->id);
            })
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }
}
