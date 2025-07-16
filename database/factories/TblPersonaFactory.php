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
        // Fiscalías válidas disponibles
        $fiscalias = [501, 502, 504, 507, 5, 6, 7, 503, 515];
        
        return [
            'Nombre' => fake()->firstName(),
            'Apellido' => fake()->lastName(),
            'UserName' => fake()->unique()->userName(),
            'cod_fiscalia' => fake()->randomElement($fiscalias),
            'id_escalafon' => 1, // Usamos el escalafón que existe
            'flag_lider' => fake()->boolean(30), // 30% de probabilidad de ser líder
        ];
    }

    /**
     * Crear una persona que puede ser líder
     */
    public function puedeSerLider(): static
    {
        return $this->state(fn (array $attributes) => [
            'flag_lider' => true,
        ]);
    }

    /**
     * Crear una persona que NO puede ser líder
     */
    public function noPuedeSerLider(): static
    {
        return $this->state(fn (array $attributes) => [
            'flag_lider' => false,
        ]);
    }
}
