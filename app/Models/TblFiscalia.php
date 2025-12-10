<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblFiscalia extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'tbl_fiscalia';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'cod_fiscalia', // Código único de la fiscalía
        'gls_fiscalia', // Nombre o descripción de la fiscalía
    ];

    // Si necesitas timestamps (created_at, updated_at), puedes habilitarlos o deshabilitarlos
    public $timestamps = false;

    // Relación con otros modelos (si aplica)
    // Por ejemplo, si hay una relación con personas:
    public function personas()
    {
        return $this->hasMany(tbl_Persona::class, 'cod_fiscalia', 'cod_fiscalia');
    }
}
