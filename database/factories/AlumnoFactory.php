<?php

namespace Database\Factories;

use App\Models\Alumno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumno>
 */
class AlumnoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'AL-' . fake()->unique()->numerify('####'),
            'dni' => fake()->unique()->numerify('########'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-18 years', '-3 years')->format('Y-m-d'),
            'sexo' => fake()->randomElement(['F', 'M']),
            'celular' => fake()->optional()->numerify('9########'),
            'email' => fake()->optional()->safeEmail(),
            'direccion' => fake()->optional()->streetAddress(),
            'estado' => 'ACTIVO',
        ];
    }
}