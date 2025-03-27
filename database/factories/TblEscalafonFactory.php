<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TblEscalafon;

class TblEscalafonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TblEscalafon::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'gls_escalafon' => fake()->word(),
        ];
    }
}
