<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Matricula;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MatriculaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_matricula(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nivel = \App\Models\Nivel::factory()->create();

        $response = $this->post('/matriculas', [
            'codigo' => 'MAT-TEST-001',
            'id_alumno' => \App\Models\Alumno::factory()->create()->id_alumno,
            'id_periodo' => \App\Models\PeriodoAcademico::factory()->create()->id_periodo,
            'id_nivel' => $nivel->id_nivel,
            'modalidad' => 'ESCOLAR',
            'id_grado' => \App\Models\Grado::factory()->create(['id_nivel' => $nivel->id_nivel])->id_grado,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('matriculas', ['codigo' => 'MAT-TEST-001']);
    }

    public function test_matricula_with_two_modalities(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nivel = \App\Models\Nivel::factory()->create();
        $periodo = \App\Models\PeriodoAcademico::factory()->create();
        $gradoEscolar = \App\Models\Grado::factory()->create(['id_nivel' => $nivel->id_nivel]);
        $gradoAcademia = \App\Models\Grado::factory()->create(['id_nivel' => $nivel->id_nivel]);

        $alumno = \App\Models\Alumno::factory()->create();

        // El ciclo debe pertenecer al mismo periodo que la matricula: la FK
        // fk_matriculas_ciclo_periodo es compuesta (id_ciclo, id_periodo).
        $ciclo = \App\Models\CicloAcademia::factory()->create([
            'id_periodo' => $periodo->id_periodo,
        ]);

        $this->post('/matriculas', [
            'codigo' => 'MAT-ESC-001',
            'id_alumno' => $alumno->id_alumno,
            'id_periodo' => $periodo->id_periodo,
            'id_nivel' => $nivel->id_nivel,
            'modalidad' => 'ESCOLAR',
            'id_grado' => $gradoEscolar->id_grado,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
        ]);

        $this->post('/matriculas', [
            'codigo' => 'MAT-ACA-001',
            'id_alumno' => $alumno->id_alumno,
            'id_periodo' => $periodo->id_periodo,
            'id_nivel' => $nivel->id_nivel,
            'modalidad' => 'ACADEMIA',
            'id_ciclo' => $ciclo->id_ciclo,
            'id_grado' => null,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
        ]);

        $this->assertDatabaseHas('matriculas', ['codigo' => 'MAT-ESC-001']);
        $this->assertDatabaseHas('matriculas', ['codigo' => 'MAT-ACA-001']);
        $this->assertCount(2, \App\Models\Matricula::where('id_alumno', $alumno->id_alumno)->get());
    }

    public function test_rechaza_un_ciclo_de_otro_periodo_en_vez_de_romper_la_fk(): void
    {
        $this->actingAs(User::factory()->create());

        $nivel = \App\Models\Nivel::factory()->create();
        $periodo = \App\Models\PeriodoAcademico::factory()->create();
        $otroPeriodo = \App\Models\PeriodoAcademico::factory()->create();
        $cicloDeOtroPeriodo = \App\Models\CicloAcademia::factory()->create([
            'id_periodo' => $otroPeriodo->id_periodo,
        ]);

        $this->post('/matriculas', [
            'codigo' => 'MAT-CICLO-CRUZADO',
            'id_alumno' => \App\Models\Alumno::factory()->create()->id_alumno,
            'id_periodo' => $periodo->id_periodo,
            'id_nivel' => $nivel->id_nivel,
            'modalidad' => 'ACADEMIA',
            'id_ciclo' => $cicloDeOtroPeriodo->id_ciclo,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
        ])->assertSessionHasErrors('id_ciclo');

        $this->assertDatabaseMissing('matriculas', ['codigo' => 'MAT-CICLO-CRUZADO']);
    }

    public function test_rechaza_un_grado_de_otro_nivel_en_vez_de_romper_la_fk(): void
    {
        $this->actingAs(User::factory()->create());

        $nivel = \App\Models\Nivel::factory()->create();
        $otroNivel = \App\Models\Nivel::factory()->create();
        $gradoDeOtroNivel = \App\Models\Grado::factory()->create([
            'id_nivel' => $otroNivel->id_nivel,
        ]);

        $this->post('/matriculas', [
            'codigo' => 'MAT-GRADO-CRUZADO',
            'id_alumno' => \App\Models\Alumno::factory()->create()->id_alumno,
            'id_periodo' => \App\Models\PeriodoAcademico::factory()->create()->id_periodo,
            'id_nivel' => $nivel->id_nivel,
            'modalidad' => 'ESCOLAR',
            'id_grado' => $gradoDeOtroNivel->id_grado,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
        ])->assertSessionHasErrors('id_grado');

        $this->assertDatabaseMissing('matriculas', ['codigo' => 'MAT-GRADO-CRUZADO']);
    }

    public function test_una_matricula_pagada_puede_editarse_sin_cambiar_de_estado(): void
    {
        $this->actingAs(User::factory()->create());

        $matricula = Matricula::factory()->create(['estado' => 'PAGADA']);

        $this->put("/matriculas/{$matricula->id_matricula}", [
            'codigo' => $matricula->codigo,
            'id_alumno' => $matricula->id_alumno,
            'id_periodo' => $matricula->id_periodo,
            'id_nivel' => $matricula->id_nivel,
            'modalidad' => $matricula->modalidad,
            'id_grado' => $matricula->id_grado,
            'id_ciclo' => $matricula->id_ciclo,
            'fecha_matricula' => $matricula->fecha_matricula instanceof \DateTimeInterface
                ? $matricula->fecha_matricula->format('Y-m-d')
                : $matricula->fecha_matricula,
            'tipo_matricula' => $matricula->tipo_matricula,
            'estado' => 'PAGADA',
            'observaciones' => $matricula->observaciones,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('matriculas', [
            'id_matricula' => $matricula->id_matricula,
            'estado' => 'PAGADA',
        ]);
    }

    public function test_no_se_puede_duplicar_matricula_activa_mismo_alumno_periodo(): void
    {
        $this->actingAs(User::factory()->create());

        $matricula = Matricula::factory()->create(['estado' => 'ACTIVA']);

        $response = $this->post('/matriculas', [
            'codigo' => 'MAT-DUPLICADO-001',
            'id_alumno' => $matricula->id_alumno,
            'id_periodo' => $matricula->id_periodo,
            'id_nivel' => $matricula->id_nivel,
            'modalidad' => 'ESCOLAR',
            'id_grado' => $matricula->id_grado,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'NUEVO',
            'estado' => 'ACTIVA',
        ]);

        $response->assertSessionHasErrors('id_alumno');
        $this->assertDatabaseMissing('matriculas', ['codigo' => 'MAT-DUPLICADO-001']);
    }

    public function test_una_matricula_retirada_no_bloquea_una_nueva(): void
    {
        $this->actingAs(User::factory()->create());

        $matricula = Matricula::factory()->create(['estado' => 'RETIRADA']);

        $response = $this->post('/matriculas', [
            'codigo' => 'MAT-REINGRESO-001',
            'id_alumno' => $matricula->id_alumno,
            'id_periodo' => $matricula->id_periodo,
            'id_nivel' => $matricula->id_nivel,
            'modalidad' => 'ESCOLAR',
            'id_grado' => $matricula->id_grado,
            'fecha_matricula' => now()->format('Y-m-d'),
            'tipo_matricula' => 'REINGRESO',
            'estado' => 'ACTIVA',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('matriculas', ['codigo' => 'MAT-REINGRESO-001']);
    }

    public function test_no_se_puede_eliminar_matricula_con_pagos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $matricula = Matricula::factory()->create();

        \App\Models\Pago::create([
            'codigo' => 'PAG-DELETE-001',
            'id_matricula' => $matricula->id_matricula,
            'id_caja' => \App\Models\Caja::factory()->create()->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);

        $response = $this->delete('/matriculas/' . $matricula->id_matricula);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('matriculas', ['id_matricula' => $matricula->id_matricula]);
    }
}
