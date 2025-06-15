<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblFeriado extends Model
{
    use HasFactory;

    protected $table = 'tbl_feriados';
    protected $fillable = ['fecha', 'descripcion'];
}
