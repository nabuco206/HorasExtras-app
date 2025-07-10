<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TblConfigHorasExtras;

class TblConfigHorasExtrasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configuraciones = [
            [
                'clave' => 'he_25_laborales',
                'descripcion' => 'Horas extras al 25% - Días laborales entre 18:00 y 21:00',
                'hora_inicio' => '18:00',
                'hora_fin' => '21:00',
                'porcentaje' => 25.00,
                'dias_semana' => [1, 2, 3, 4, 5], // Lunes a Viernes
                'aplica_feriados' => false,
                'aplica_fines_semana' => false,
                'activo' => true,
                'orden' => 1
            ],
            [
                'clave' => 'he_50_laborales_noche',
                'descripcion' => 'Horas extras al 50% - Días laborales después de 21:00',
                'hora_inicio' => '21:00',
                'hora_fin' => '23:59',
                'porcentaje' => 50.00,
                'dias_semana' => [1, 2, 3, 4, 5], // Lunes a Viernes
                'aplica_feriados' => false,
                'aplica_fines_semana' => false,
                'activo' => true,
                'orden' => 2
            ],
            [
                'clave' => 'he_50_laborales_madrugada',
                'descripcion' => 'Horas extras al 50% - Días laborales antes de 18:00',
                'hora_inicio' => '00:00',
                'hora_fin' => '17:59',
                'porcentaje' => 50.00,
                'dias_semana' => [1, 2, 3, 4, 5], // Lunes a Viernes
                'aplica_feriados' => false,
                'aplica_fines_semana' => false,
                'activo' => true,
                'orden' => 3
            ],
            [
                'clave' => 'he_50_feriados',
                'descripcion' => 'Horas extras al 50% - Feriados (todo el día)',
                'hora_inicio' => null,
                'hora_fin' => null,
                'porcentaje' => 50.00,
                'dias_semana' => null,
                'aplica_feriados' => true,
                'aplica_fines_semana' => false,
                'activo' => true,
                'orden' => 4
            ],
            [
                'clave' => 'he_50_fines_semana',
                'descripcion' => 'Horas extras al 50% - Fines de semana (todo el día)',
                'hora_inicio' => null,
                'hora_fin' => null,
                'porcentaje' => 50.00,
                'dias_semana' => null,
                'aplica_feriados' => false,
                'aplica_fines_semana' => true,
                'activo' => true,
                'orden' => 5
            ]
        ];

        foreach ($configuraciones as $config) {
            TblConfigHorasExtras::updateOrCreate(
                ['clave' => $config['clave']],
                $config
            );
        }
    }
}
