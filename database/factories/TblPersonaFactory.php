<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TblPersona;
use App\Models\TblSolicitudHe;

class TblPersonaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TblPersona::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'Nombre' => fake()->word(),
            'Apellido' => fake()->word(),
            'UserName' => TblSolicitudHe::factory()->create()->username,
            'cod_fiscalia' => fake()->numberBetween(-100000, 100000),
            'id_escalafon' => fake()->numberBetween(-100000, 100000),
        ];
    }
}
