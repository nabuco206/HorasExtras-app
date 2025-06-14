<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

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

    protected static function booted()
    {
        static::created(function ($persona) {
            User::create([
                'name' => $persona->UserName,
                'email' => $persona->UserName . '@minpublico.cl',
                'password' => bcrypt('1234'),
                'persona_id' => $persona->id,
                'rol' => 1,
            ]);
        });
    }

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

    public function fiscalia()
    {
        return $this->belongsTo(TblFiscalia::class, 'cod_fiscalia', 'id'); // o 'codigo' si corresponde
    }

    public function escalafon()
    {
        return $this->belongsTo(TblEscalafon::class, 'id_escalafon', 'id'); // o 'codigo' si corresponde
    }
}
