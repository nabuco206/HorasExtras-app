<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TblSolicitudHe;
use Faker\Factory as Faker;

class TblSolicitudHeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            TblSolicitudHe::create([
                'username' => $faker->userName,
                'cod_fiscalia' => $faker->numberBetween(1, 10),
                'id_tipo_trabajo' => $faker->numberBetween(1, 5),
                'fecha' => $faker->date(),
                'hrs_inicial' => $faker->time('H:i'),
                'hrs_final' => $faker->time('H:i'),
                'id_estado' => 1,
                'tipo_solicitud' => $faker->randomLetter(),
                'fecha_evento' => $faker->date(),
                'hrs_inicio' => $faker->time('H:i'),
                'hrs_fin' => $faker->time('H:i'),
                'id_tipoCompensacion' => $faker->numberBetween(1, 5),
                'min_reales' => $faker->numberBetween(0, 1000),
                'min_25' => $faker->numberBetween(0, 1000),
                'min_50' => $faker->numberBetween(0, 1000),
                'total_min' => $faker->numberBetween(0, 1000),
            ]);
        }
    }
}
