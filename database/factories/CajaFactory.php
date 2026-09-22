<?php

namespace Database\Factories;

use App\Models\Caja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'CAJ-' . fake()->unique()->numerify('###'),
            'nombre' => fake()->unique()->randomElement(['Caja Principal', 'Caja Secundaria', 'Banco BCP']),
            'tipo' => fake()->randomElement(['EFECTIVO', 'BANCO', 'BILLETERA_DIGITAL']),
            'estado' => 'ACTIVA',
        ];
    }
}