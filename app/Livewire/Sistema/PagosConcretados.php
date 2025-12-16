<?php

// app/Http/Livewire/Sistema/PagosConcretados.php

namespace App\Livewire\Sistema;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use App\Models\TblPagoConcretado;

use Illuminate\Support\Facades\Log;

class PagosConcretados extends Component
{
    use WithFileUploads;

    public $archivoAdjunto;
    public $archivoCargado = false;
    public $cargandoArchivo = false;
    public $excelData = [];

    protected $rules = [
        'archivoAdjunto' => 'nullable|file|max:5120|mimes:xls,xlsx',
    ];

    public function updatedArchivoAdjunto()
    {
        $this->cargandoArchivo = true;
        $this->validateOnly('archivoAdjunto');
        $this->archivoCargado = $this->archivoAdjunto;

        // Leer el archivo Excel y guardar los datos en $excelData
        if ($this->archivoAdjunto) {
            $path = $this->archivoAdjunto->getRealPath();
            $data = Excel::toArray([], $this->archivoAdjunto);
            $this->excelData = $data[0] ?? [];
        } else {
            $this->excelData = [];
        }

        $this->cargandoArchivo = false;
    }

    public function guardarPagosConcretados()
    {
        if (empty($this->excelData) || count($this->excelData) < 2) {
            session()->flash('error', 'No hay datos para insertar.');
            return;
        }

        $headers = $this->excelData[0];
        $rows = array_slice($this->excelData, 1);
        $insertados = 0;

        foreach ($rows as $row) {
            if (count($row) < 7) continue; // Evitar filas incompletas
            try {
                // Convertir fecha Excel si es numérica
                $fechaExcel = $row[1];
                if (is_numeric($fechaExcel)) {
                    // Excel date to Y-m-d
                    $unixDate = ($fechaExcel - 25569) * 86400;
                    $fecha_pago = gmdate('Y-m-d', $unixDate);
                } else {
                    $fecha_pago = $fechaExcel;
                }
                log::info($row[0]." - ".$fecha_pago." - ".$row[2]." - ".$row[3]." - ".$row[4]." - ".$row[5]." - ".$row[6]." - ".$row[7]);
                TblPagoConcretado::create([
                    'sociedad_id' => $row[0],
                    'fecha_pago' => $fecha_pago,
                    'id_empleado' => $row[2],
                    'rut' => $row[3],
                    'nombre' => $row[4],
                    'sobretiempo_normal_25' => $row[5],
                    'moneda_id' => $row[6],
                    'sobretiempo_especial_50' => $row[7],
                ]);
                 
                $insertados++;
            } catch (\Exception $e) {
                // Puedes loguear el error si lo deseas
                log::error('Error al insertar fila: ' . $e->getMessage());
                continue;
            }
        }
        session()->flash('success', "Pagos insertados: $insertados");
    }

    public function render()
    {
        return view('livewire.sistema.pagos-concretados', [
            'excelData' => $this->excelData,
        ]);
    }
}
