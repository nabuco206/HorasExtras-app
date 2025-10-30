<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/TblEstado.php
class TblEstado extends Model
{
    protected $table = 'tbl_estados';
    protected $fillable = ['codigo', 'descripcion', 'tipo_accion', 'flujo', 'es_final', 'activo'];

    public function transicionesOrigen()
    {
        return $this->hasMany(TblFlujoEstado::class, 'estado_origen_id');
    }

    public function transicionesDestino()
    {
        return $this->hasMany(TblFlujoEstado::class, 'estado_destino_id');
    }

    // Obtener posibles estados siguientes para este estado en un flujo específico
    public function siguientesEstados($flujoId)
    {
        return $this->transicionesOrigen()
            ->where('flujo_id', $flujoId)
            ->where('activo', true)
            ->with('estadoDestino')
            ->get()
            ->pluck('estadoDestino');
    }

    // Obtener posibles estados anteriores para este estado en un flujo específico
    public function anterioresEstados($flujoId)
    {
        return $this->transicionesDestino()
            ->where('flujo_id', $flujoId)
            ->where('activo', true)
            ->with('estadoOrigen')
            ->get()
            ->pluck('estadoOrigen');
    }

    // Verificar si es un estado inicial en algún flujo
    public function esEstadoInicial($flujoId = null)
    {
        $query = $this->transicionesDestino();

        if ($flujoId) {
            $query->where('flujo_id', $flujoId);
        }

        return !$query->exists();
    }

    // Verificar si puede transicionar a otro estado con un rol específico
    public function puedeTransicionarA($estadoDestinoId, $flujoId, $rol = null)
    {
        $query = $this->transicionesOrigen()
            ->where('flujo_id', $flujoId)
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
}

