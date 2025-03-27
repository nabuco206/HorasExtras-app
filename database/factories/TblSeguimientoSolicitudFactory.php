<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\TblEstado;
use App\Models\TblSeguimientoSolicitud;

class TblSeguimientoSolicitudFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TblSeguimientoSolicitud::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_solicitud_he' => fake()->numberBetween(-100000, 100000),
            'username' => fake()->userName(),
            'id_estado' => TblEstado::factory(),
        ];
    }
}
