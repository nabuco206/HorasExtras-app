<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TblPersona extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Nombre',
        'Apellido',
        'UserName',
        'cod_fiscalia',
        'id_escalafon',
        'flag_lider',
        'flag_activo',
    ];

    protected static function booted()
    {
        static::created(function ($persona) {
            User::create([
                'name' => $persona->UserName,
                'email' => $persona->UserName . '@minpublico.cl',
                'password' => bcrypt('1234'),
                'persona_id' => $persona->id,
                'id_rol' => 0,
            ]);
        });
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cod_fiscalia' => 'integer',
        'id_escalafon' => 'integer',
        'flag_lider' => 'boolean',
        'flag_activo' => 'boolean',
    ];

    public function tblSolicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'UserName', 'username');
    }

    public function fiscalia()
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'id'); // o 'codigo' si corresponde
    }

    public function escalafon()
    {
        return $this->belongsTo(TblEscalafon::class, 'id_escalafon', 'id'); // o 'codigo' si corresponde
    }

    /**
     * Verifica si la persona puede ser líder
     *
     * @return bool
     */
    public function puedeSerLider(): bool
    {
        return $this->flag_lider === true;
    }

    /**
     * Verifica si la persona está activa en el sistema
     *
     * @return bool
     */
    public function estaActiva(): bool
    {
        return $this->flag_activo === true;
    }

    /**
     * Scope para obtener solo las personas que pueden ser líderes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePuedenSerLideres($query)
    {
        return $query->where('flag_lider', true);
    }

    /**
     * Scope para obtener solo las personas activas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivas($query)
    {
        return $query->where('flag_activo', true);
    }
}
