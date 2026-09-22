<?php

namespace Database\Factories;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grado>
 */
class GradoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_nivel' => Nivel::factory(),
            'nombre' => fake()->unique()->randomElement(['1°', '2°', '3°', '4°', '5°']),
            'orden' => fake()->unique()->numberBetween(1, 20),
            'estado' => 'ACTIVO',
        ];
    }
}