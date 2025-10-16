<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TblSolicitudHe;
use Carbon\Carbon;

class TblSolicitudHeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear las dos solicitudes específicas según los datos proporcionados

        // Solicitud #1: EIVG del 07/10/2025, 18:00-19:00, 75 minutos totales
        // TblSolicitudHe::create([
        //     'username' => 'persona01',
        //     'cod_fiscalia' => 501, // Fiscalia de Valparaiso
        //     'id_tipo_trabajo' => 0, // EIVG
        //     'fecha' => '2025-10-07',
        //     'hrs_inicial' => '18:00',
        //     'hrs_final' => '19:00',
        //     'id_estado' => 0, // Ingreso
        //     'id_tipo_compensacion' => 0, // Tiempo (Compensación en Hrs)
        //     'min_reales' => 60,
        //     'min_25' => 15,
        //     'min_50' => 0,
        //     'total_min' => 75,
        // ]);

        // // Solicitud #2: Idéntica a la anterior (según la tabla proporcionada)
        // TblSolicitudHe::create([
        //     'username' => 'persona01',
        //     'cod_fiscalia' => 501, // Fiscalia de Valparaiso
        //     'id_tipo_trabajo' => 0, // EIVG
        //     'fecha' => '2025-10-07',
        //     'hrs_inicial' => '18:00',
        //     'hrs_final' => '19:00',
        //     'id_estado' => 0, // Ingreso
        //     'id_tipo_compensacion' => 0, // Tiempo (Compensación en Hrs)
        //     'min_reales' => 60,
        //     'min_25' => 15,
        //     'min_50' => 0,
        //     'total_min' => 75,
        // ]);
    }
}
