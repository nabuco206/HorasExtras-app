<?php

namespace App\Imports;

use App\Models\PagoConcretado;
use Maatwebsite\Excel\Concerns\ToModel;

class PagosConcretadosImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new PagoConcretado([
            'sociedad_id' => $row[0],
            'fecha_pago' => $row[1],
            'rut_id' => $row[2],
            'nombre' => $row[3],
            'sobretiempo_normal' => $row[4],
            'moneda_id' => $row[5],
            'sobretiempo_especial' => $row[6],
        ]);
    }
}