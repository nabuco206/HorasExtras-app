<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblPersona extends Authenticatable
{
    use HasFactory;

    protected $table = 'tbl_personas';

    protected $fillable = [
        'nombre',
        'apellido',
        'username',
        'rut',
        'cod_fiscalia',
        'id_escalafon',
        'flag_lider',
        'flag_activo',
        'password',
        'id_rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'integer',
        'cod_fiscalia' => 'integer',
        'id_escalafon' => 'integer',
        'flag_lider' => 'boolean',
        'flag_activo' => 'boolean',
    ];

    /**
     * Accesor para el atributo "name" requerido por Filament.
     * Siempre retorna el username o un string fijo si está vacío.
     */
    public function getNameAttribute(): string
    {
        // Ahora usa username en minúsculas
        return $this->username ?? 'Usuario Filament';
    }

    /**
     * Forzar el identificador de autenticación a username para Filament y Laravel
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function tblSolicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'username', 'username');
    }

    public function fiscalia()
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'cod_fiscalia');
    }

    public function escalafon()
    {
        return $this->belongsTo(TblEscalafon::class, 'id_escalafon', 'id');
    }

    public function puedeSerLider(): bool
    {
        return $this->flag_lider === true;
    }

    public function estaActiva(): bool
    {
        return $this->flag_activo === true;
    }

    public function scopePuedenSerLideres($query)
    {
        return $query->where('flag_lider', true);
    }

    public function scopeActivas($query)
    {
        return $query->where('flag_activo', true);
    }

    public function initials(): string
    {
        $nombreCompleto = $this->nombre . ' ' . $this->apellido;
        return collect(explode(' ', $nombreCompleto))
            ->map(fn ($parte) => mb_substr($parte, 0, 1))
            ->implode('');
    }

    // Métodos requeridos para Filament y autenticación
    public function getUsernameAttribute(): string
    {
        // Siempre retorna string no vacío, nunca null
        $username = (string) ($this->attributes['username'] ?? '');
        return $username !== '' ? $username : 'SinNombre';
    }

    public function getUserName(): string
    {
        // Log para depuración
        $username = $this->username ?? 'null';
        \Log::info('[Filament][getUserName] Instancia: ' . get_class($this) . ' | username: ' . $username);
        return $username ?: 'SinNombre';
    }

    public function username(): string
    {
        return $this->username;
    }
}
