<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TblTurno;

class TblTurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turnos = [
            [
                'gls_turno' => 'Turno Mañana',
                'hora_inicio' => '08:00:00',
                'hora_fin' => '17:00:00',
                'activo' => true,
            ],
            [
                'gls_turno' => 'Turno Tarde',
                'hora_inicio' => '14:00:00',
                'hora_fin' => '23:00:00',
                'activo' => true,
            ],
            [
                'gls_turno' => 'Turno Noche',
                'hora_inicio' => '22:00:00',
                'hora_fin' => '07:00:00',
                'activo' => true,
            ],
        ];

        foreach ($turnos as $turno) {
            TblTurno::firstOrCreate(
                ['gls_turno' => $turno['gls_turno']],
                $turno
            );
        }
    }
}
