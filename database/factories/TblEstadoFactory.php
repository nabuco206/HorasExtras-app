<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TblEstado;

class TblEstadoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TblEstado::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'gls_estado' => fake()->word(),
        ];
    }
}
