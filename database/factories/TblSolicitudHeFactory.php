<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TblEstado;
use App\Models\TblSolicitudHe;

class TblSolicitudHeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TblSolicitudHe::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'username' => fake()->userName(),
            'tipo_trabajo' => fake()->numberBetween(-100000, 100000),
            'fecha' => fake()->date(),
            'hrs_inicial' => fake()->time(),
            'hrs_final' => fake()->time(),
            'id_estado' => TblEstado::factory(),
            'tipo_solicitud' => fake()->randomLetter(),
            'fecha_evento' => fake()->date(),
            'hrs_inicio' => fake()->time(),
            'hrs_fin' => fake()->time(),
            'id_tipoCompensacion' => fake()->numberBetween(-100000, 100000),
            'min_25' => fake()->numberBetween(-100000, 100000),
            'min_50' => fake()->numberBetween(-100000, 100000),
            'total_min' => fake()->numberBetween(-100000, 100000),
        ];
    }
}
