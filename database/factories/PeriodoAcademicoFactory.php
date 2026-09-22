<?php

namespace Database\Factories;

use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoAcademico>
 */
class PeriodoAcademicoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => 'P' . fake()->unique()->year(),
            'nombre' => 'Periodo ' . fake()->year(),
            'anio' => fake()->year(),
            'fecha_inicio' => fn () => now()->startOfYear(),
            'fecha_fin' => fn () => now()->endOfYear(),
            'estado' => 'ABIERTO',
        ];
    }
}