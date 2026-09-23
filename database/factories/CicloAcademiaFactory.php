<?php

namespace Database\Factories;

use App\Models\CicloAcademia;
use App\Models\PeriodoAcademico;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicloAcademia>
 */
class CicloAcademiaFactory extends Factory
{
    protected $model = CicloAcademia::class;

    public function definition(): array
    {
        return [
            'id_periodo' => PeriodoAcademico::factory(),
            'id_facultad' => Facultad::factory(),
            'nombre' => fake()->word(),
            'turno' => fake()->randomElement(['MANANA', 'TARDE', 'NOCHE', 'COMPLETO']),
            'fecha_inicio' => fake()->date(),
            'fecha_fin' => fake()->date(),
            'monto_referencial' => fake()->randomFloat(2, 100, 5000),
            'vacantes' => fake()->randomDigitNotNull(),
            'estado' => fake()->randomElement(['PLANIFICADO', 'ABIERTO', 'CERRADO', 'CANCELADO']),
        ];
    }
}
