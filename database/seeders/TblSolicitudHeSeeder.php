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

        // Solicitud especial para devoluciones de compensaciones rechazadas
        TblSolicitudHe::create([
            'id' => 999,
            'username' => 'SISTEMA',
            'cod_fiscalia' => 1,
            'id_tipo_trabajo' => 1,
            'fecha' => '2025-01-01',
            'hrs_inicial' => '00:00:00',
            'hrs_final' => '00:00:00',
            'id_estado' => 1, // INGRESADO
            'id_tipo_compensacion' => 1,
            'min_reales' => 0,
            'min_25' => 0,
            'min_50' => 0,
            'total_min' => 0,
        ]);

        // Crear algunas solicitudes de prueba para demostrar el sistema
        $usuarios = ['maria.gonzalez', 'carlos.rodriguez', 'ana.lopez', 'luis.martinez', 'carmen.morales'];
        $fechas = [
            now()->subDays(5)->format('Y-m-d'),
            now()->subDays(3)->format('Y-m-d'),
            now()->subDays(1)->format('Y-m-d'),
            now()->format('Y-m-d'),
        ];

        $contador = 1;
        foreach ($usuarios as $username) {
            foreach ($fechas as $key => $fecha) {
                if ($contador > 10) break; // Limitar a 10 solicitudes

                $horasExtras = [
                    ['08:00:00', '10:00:00', 120], // 2 horas
                    ['18:00:00', '20:30:00', 150], // 2.5 horas
                    ['14:00:00', '16:00:00', 120], // 2 horas
                    ['09:00:00', '12:00:00', 180], // 3 horas
                ];

                $he = $horasExtras[$key % count($horasExtras)];

                TblSolicitudHe::create([
                    'username' => $username,
                    'cod_fiscalia' => 501,
                    'id_tipo_trabajo' => 1, // Asumiendo que existe
                    'fecha' => $fecha,
                    'hrs_inicial' => $he[0],
                    'hrs_final' => $he[1],
                    'id_estado' => $contador <= 6 ? 1 : ($contador <= 8 ? 3 : 4), // Variado: pendientes, aprobadas, rechazadas
                    'id_tipo_compensacion' => 1, // HE_COMPENSACION
                    'min_reales' => $he[2],
                    'min_25' => intval($he[2] * 0.25),
                    'min_50' => 0,
                    'total_min' => $he[2] + intval($he[2] * 0.25),
                ]);

                $contador++;
            }
        }

        // Crear algunas solicitudes adicionales para usuarios de prueba
        for ($i = 1; $i <= 5; $i++) {
            TblSolicitudHe::create([
                'username' => "user_test_{$i}",
                'cod_fiscalia' => 501,
                'id_tipo_trabajo' => 1,
                'fecha' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                'hrs_inicial' => '08:00:00',
                'hrs_final' => '10:00:00',
                'id_estado' => 1, // INGRESADO - listas para aprobar
                'id_tipo_compensacion' => 1,
                'min_reales' => 120,
                'min_25' => 30,
                'min_50' => 0,
                'total_min' => 150,
            ]);
        }
    }
}
