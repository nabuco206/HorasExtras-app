<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TblBolsonTiempo;
use App\Models\TblSolicitudHe;

class TblBolsonTiempoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TblBolsonTiempo::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_solicitud' => TblSolicitudHe::factory(),
            'tiempo' => fake()->numberBetween(-100000, 100000),
            'estado' => fake()->randomLetter(),
        ];
    }
}
