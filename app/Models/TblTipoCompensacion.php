<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblTipoCompensacion extends Model
{
    use HasFactory;
    protected $table = 'tbl_tipo_compensaciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gls_tipo_compensacion',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'gls_tipo_compensacion' => 'string',
    ];

    public function tblSolicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'id', 'id_tipo_compensacion');
    }
}
