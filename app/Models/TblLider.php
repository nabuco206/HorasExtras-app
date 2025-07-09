<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblLider extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'persona_id',
        'cod_fiscalia',
        'gls_unidad',
        'activo',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cod_fiscalia' => 'integer',
        'persona_id' => 'integer',
    ];

    /**
     * Relación con TblFiscalia
     */
    public function fiscalia(): BelongsTo
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'id');
    }

    /**
     * Relación con TblPersona
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(TblPersona::class, 'persona_id', 'id');
    }
}
