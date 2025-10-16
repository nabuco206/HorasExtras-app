<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
