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
        'Nombre',
        'Apellido',
        'UserName',
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
     * Siempre retorna el UserName o un string fijo si está vacío.
     */
    public function getNameAttribute(): string
    {
        // Puedes cambiar 'Usuario Filament' por $this->UserName si prefieres mostrar el username real
        return $this->UserName ?? 'Usuario Filament';
    }

    /**
     * Forzar el identificador de autenticación a UserName para Filament y Laravel
     */
    public function getAuthIdentifierName()
    {
        return 'UserName';
    }

    public function tblSolicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'UserName', 'username');
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
        $nombreCompleto = $this->Nombre . ' ' . $this->Apellido;
        return collect(explode(' ', $nombreCompleto))
            ->map(fn ($parte) => mb_substr($parte, 0, 1))
            ->implode('');
    }

    // Métodos requeridos para Filament y autenticación
    public function getUserNameAttribute(): string
    {
        // Siempre retorna string no vacío, nunca null
        $username = (string) ($this->attributes['UserName'] ?? '');
        return $username !== '' ? $username : 'SinNombre';
    }

    public function getUserName(): string
    {
        // Log para depuración
        $username = $this->UserName ?? 'null';
        \Log::info('[Filament][getUserName] Instancia: ' . get_class($this) . ' | UserName: ' . $username);
        return $username ?: 'SinNombre';
    }

    public function username(): string
    {
        return $this->UserName;
    }
}
