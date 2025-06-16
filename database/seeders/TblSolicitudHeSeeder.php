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
                // Ajusta los campos según tu tabla
                'fecha' => $faker->date(),
                'hrs_inicial' => $faker->time('H:i'),
                'hrs_final' => $faker->time('H:i'),
                'username' => $faker->userName,
                // ...otros campos necesarios
            ]);
        }
    }
}
