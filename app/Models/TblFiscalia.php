<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TblFiscalia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'cod_fiscalia';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = [
        'cod_fiscalia',
        'gls_fiscalia',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cod_fiscalia' => 'integer',
    ];

    // Si tienes relaciones, ajústalas aquí según corresponda
}
