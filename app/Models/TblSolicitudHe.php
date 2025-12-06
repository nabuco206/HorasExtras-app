<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class TblSolicitudHe extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_solicitud_hes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'cod_fiscalia',
        'id_tipo_trabajo',
        'fecha',
        'hrs_inicial',
        'hrs_final',
        'id_estado',
        'id_tipo_compensacion',
        'min_reales',
        'min_25',
        'min_50',
        'total_min',
        'archivo_adjunto',
        'nombre_archivo_original',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'id_tipo_trabajo' => 'integer',
        'fecha' => 'date',
        'id_estado' => 'integer',
        'fecha_evento' => 'date',
        'id_tipo_compensacion' => 'integer',
        'min_reales' => 'integer',
        'min_25' => 'integer',
        'min_50' => 'integer',
        'total_min' => 'integer',
    ];

    /**
     * Obtener la URL del archivo adjunto
     */
    public function getArchivoUrlAttribute(): ?string
    {
        if (!$this->archivo_adjunto) {
            return null;
        }

        return asset('storage/solicitudes-he/' . $this->archivo_adjunto);
    }

    /**
     * Verificar si tiene archivo adjunto
     */
    public function tieneArchivo(): bool
    {
        return !empty($this->archivo_adjunto) && \Storage::disk('public')->exists('solicitudes-he/' . $this->archivo_adjunto);
    }

    /**
     * Obtener la URL del nombre del archivo original
     */
    public function getNombreArchivoOriginalUrlAttribute(): ?string
    {
        if (!$this->nombre_archivo_original) {
            return null;
        }

        return asset('storage/solicitudes-he/' . $this->nombre_archivo_original);
    }

    public function tblSeguimientoSolicitud(): BelongsTo
    {
        return $this->belongsTo(TblSeguimientoSolicitud::class, 'id', 'id_solicitud_he');
    }

    public function idEstado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'id_estado');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'name');
    }

    public function tipoTrabajo(): BelongsTo
    {
        return $this->belongsTo(TblTipoTrabajo::class, 'id_tipo_trabajo');
    }

    public function tipoCompensacion(): BelongsTo
    {
        return $this->belongsTo(TblTipo_Compensacion::class, 'id_tipo_compensacion');
    }

    public function bolsonTiempo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TblBolsonTiempo::class, 'id_solicitud_he');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(TblEstado::class, 'id_estado');
    }

    public function seguimientos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TblSeguimientoSolicitud::class, 'id_solicitud_he', 'id');
    }

    public function fiscalia(): BelongsTo
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'cod_fiscalia');
    }

    /**
     * Debug method to check if username is accessible
     */
    public function hasUsername(): bool
    {
        return isset($this->attributes['username']);
    }

    /**
     * Get username safely
     */
    public function getUsername(): ?string
    {
        return $this->attributes['username'] ?? null;
    }

    /**
     * Obtener el flujo asociado a este tipo de solicitud
     */
    public function obtenerFlujo(): ?TblFlujo
    {
        // Determinar el tipo de flujo basado en el tipo de compensación
        $tipoFlujo = match($this->id_tipo_compensacion) {
            1 => 'HE_COMPENSACION',
            2 => 'HE_DINERO',
            default => 'HE_COMPENSACION'
        };

        return TblFlujo::where('nombre', $tipoFlujo)->first();
    }

    /**
     * Obtener las transiciones disponibles para el estado actual
     */
    public function transicionesDisponibles($rol = null): \Illuminate\Support\Collection
    {
        $flujo = $this->obtenerFlujo();

        if (!$flujo) {
            return collect();
        }

        $flujoService = app(\App\Services\FlujoEstadoService::class);
        return $flujoService->obtenerTransicionesDisponibles($flujo->id, $this->id_estado, $rol);
    }

    /**
     * Verificar si se puede transicionar a un estado específico
     */
    public function puedeTransicionarA($estadoDestinoId, $rol = null): array
    {
        $flujo = $this->obtenerFlujo();

        if (!$flujo) {
            return [
                'valida' => false,
                'mensaje' => 'No se encontró flujo para esta solicitud'
            ];
        }

        $flujoService = app(\App\Services\FlujoEstadoService::class);
        return $flujoService->validarTransicion($flujo->id, $this->id_estado, $estadoDestinoId, $rol, $this);
    }

    /**
     * Ejecutar transición a un nuevo estado
     */
    public function transicionarA($estadoDestinoId, $usuarioId, $observaciones = null): array
    {
        $flujoService = app(\App\Services\FlujoEstadoService::class);
        return $flujoService->ejecutarTransicion($this, $estadoDestinoId, $usuarioId, $observaciones);
    }

    /**
     * Verificar si la solicitud está en un estado final
     */
    public function estaEnEstadoFinal(): bool
    {
        $flujo = $this->obtenerFlujo();

        if (!$flujo) {
            return false;
        }

        $estadosFinales = $flujo->estadosFinales();
        return $estadosFinales->contains('id', $this->id_estado);
    }

    /**
     * Verificar si la solicitud está en un estado inicial
     */
    public function estaEnEstadoInicial(): bool
    {
        $flujo = $this->obtenerFlujo();

        if (!$flujo) {
            return false;
        }

        $estadosIniciales = $flujo->estadosIniciales();
        return $estadosIniciales->contains('id', $this->id_estado);
    }

    /**
     * Obtener el historial de transiciones de esta solicitud
     */
    public function historialTransiciones(): \Illuminate\Support\Collection
    {
        return $this->seguimientos()
            ->with(['estadoAnterior', 'estadoNuevo', 'usuario'])
            ->orderBy('fecha_cambio')
            ->get();
    }

    /**
     * Alias para compatibilidad con código existente
     */
    public function getTotalMinutosAttribute()
    {
        return $this->total_min;
    }

    public function verEstados($idSolicitud): void
    {
        $seguimientos = \App\Models\TblSeguimientoSolicitud::where('id_solicitud_he', $idSolicitud)
            ->with('estado')
            ->orderBy('created_at')
            ->get();

        $estados = [];

        foreach ($seguimientos as $seguimiento) {
            $estados[] = [
                'id' => $seguimiento->id,
                'gls_estado' => $seguimiento->id_estado == 0
                    ? 'Ingresado'
                    : ($seguimiento->estado->gls_estado ?? 'Desconocido'),
                'created_at' => $seguimiento->created_at->format('d/m/Y H:i'),
            ];
        }

        $this->estadosSolicitud = $estados;
        $this->modalEstadosVisible = true;
    }

    /**
     * Scope para filtrar solicitudes según el rol del usuario.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $rol
     * @param string $username
     * @param string|null $codFiscalia
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorRol($query, $rol, $username, $codFiscalia = null)
    {
        // Normalizar rol a entero cuando sea posible
        $rolInt = is_numeric($rol) ? (int) $rol : $rol;

        // Roles sin filtro: UDP (3), JUDP (4), DER (5)
        if (in_array($rolInt, [3, 4, 5], true)) {
            return $query; // sin restricciones
        }

        // Jefe Directo (2): filtrar por cod_fiscalia
        if ($rolInt === 2) {
            if ($codFiscalia !== null) {
                return $query->where('cod_fiscalia', $codFiscalia);
            }
            // Si no tenemos codFiscalia proporcionado, devolver consulta vacía como medida de seguridad
            return $query->whereRaw('1 = 0');
        }

        // Usuario (1): ver solo sus propias solicitudes (username)
        if ($rolInt === 1) {
            return $query->where('username', $username);
        }

        // Por defecto (otros roles): comportarse como usuario normal
        return $query->where('username', $username);
    }
}
