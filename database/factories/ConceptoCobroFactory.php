<?php

namespace Database\Factories;

use App\Models\ConceptoCobro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConceptoCobro>
 */
class ConceptoCobroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'CON-' . fake()->unique()->numerify('###'),
            'nombre' => fake()->unique()->randomElement(['Matrícula', 'Mensualidad', 'Materiales', 'Examen']),
            'tipo' => fake()->randomElement(['MATRICULA', 'MENSUALIDAD', 'MATERIAL', 'EXAMEN', 'OTRO']),
            'modalidad_aplicable' => 'AMBOS',
            'monto_referencial' => fake()->randomFloat(2, 50, 500),
            'estado' => 'ACTIVO',
        ];
    }
}