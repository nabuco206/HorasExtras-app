<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TblPersona extends Authenticatable
{
    use HasFactory;

    protected $table = 'tbl_personas';
   
    public function initials(): string
{
    // Ajusta el campo que contiene el nombre si tu modelo usa otro (p. ej. 'nombre', 'nombres', 'name')
    $fullName = trim($this->name ?? $this->nombre ?? $this->nombres ?? '');

    if ($fullName === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }

    return $initials;
}

    protected $fillable = [
        'nombre',
        'apellido',
        'username',
        'password',
        'cod_fiscalia',
        'id_escalafon',
        'flag_lider',
        'flag_activo',
        'id_rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'flag_lider' => 'boolean',
        'flag_activo' => 'boolean',
    ];

    /**
     * Nombre visible para Filament
     */
    public function getNameAttribute(): string
    {
        return $this->username;
    }
}
