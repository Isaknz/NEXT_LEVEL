<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matricula>
 */
class MatriculaFactory extends Factory
{
    public function definition(): array
    {
        $nivel = Nivel::factory()->create();

        return [
            'codigo' => 'MAT-' . fake()->unique()->numerify('####'),
            'id_alumno' => Alumno::factory(),
            'id_periodo' => PeriodoAcademico::factory(),
            'id_nivel' => $nivel->id_nivel,
            'modalidad' => 'ESCOLAR',
            'id_grado' => Grado::factory()->create(['id_nivel' => $nivel->id_nivel])->id_grado,
            'id_ciclo' => null,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
            'registrado_por' => User::factory(),
        ];
    }
}