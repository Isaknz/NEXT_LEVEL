<?php

namespace Database\Factories;

use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facultad>
 */
class FacultadFactory extends Factory
{
    protected $model = Facultad::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'estado' => 'ACTIVO',
        ];
    }
}
