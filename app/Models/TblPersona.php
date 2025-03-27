<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cod_fiscalia' => 'integer',
        'id_escalafon' => 'integer',
    ];

    public function tblSolicitudHe(): BelongsTo
    {
        return $this->belongsTo(TblSolicitudHe::class, 'UserName', 'username');
    }
}
