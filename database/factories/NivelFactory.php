<?php

namespace Database\Factories;

use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nivel>
 */
class NivelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'N-' . fake()->unique()->lexify('???'),
            'nombre' => fake()->unique()->randomElement(['PRIMARIA', 'SECUNDARIA', 'ACADEMIA']),
            'estado' => 'ACTIVO',
        ];
    }
}