<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CategoriaGasto;
use App\Models\CicloAcademia;
use App\Models\ConceptoCobro;
use App\Models\Facultad;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Nivel;
use App\Models\PeriodoAcademico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Cubre el CRUD completo de los módulos de catálogo que antes eran stubs:
 * levels, grados, facultades, periodos, conceptos, categorías y ciclos.
 */
class CatalogosCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        return $user;
    }

    // ---------------------------------------------------------------- NIVELES

    public function test_nivel_store_persiste_en_la_base(): void
    {
        $this->admin();

        $this->post(route('niveles.store'), [
            'codigo' => 'NIV-TEST',
            'nombre' => 'NIVEL DE PRUEBA',
            'estado' => 'ACTIVO',
        ])->assertRedirect(route('niveles.index'));

        $this->assertDatabaseHas('niveles', [
            'codigo' => 'NIV-TEST',
            'nombre' => 'NIVEL DE PRUEBA',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_nivel_rechaza_codigo_duplicado(): void
    {
        $this->admin();
        Nivel::factory()->create(['codigo' => 'NIV-DUP', 'nombre' => 'Original']);

        $this->post(route('niveles.store'), [
            'codigo' => 'NIV-DUP',
            'nombre' => 'Otro nombre',
            'estado' => 'ACTIVO',
        ])->assertSessionHasErrors('codigo');

        $this->assertDatabaseMissing('niveles', ['nombre' => 'Otro nombre']);
    }

    public function test_nivel_rechaza_estado_invalido(): void
    {
        $this->admin();

        $this->post(route('niveles.store'), [
            'codigo' => 'NIV-EST',
            'nombre' => 'Con estado raro',
            'estado' => 'PENDIENTE',
        ])->assertSessionHasErrors('estado');
    }

    public function test_nivel_update_persiste(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create(['codigo' => 'NIV-OLD', 'nombre' => 'Nombre viejo', 'estado' => 'ACTIVO']);

        $this->put(route('niveles.update', $nivel), [
            'codigo' => 'NIV-NEW',
            'nombre' => 'Nombre nuevo',
            'estado' => 'INACTIVO',
        ])->assertRedirect(route('niveles.index'));

        $this->assertDatabaseHas('niveles', [
            'id_nivel' => $nivel->id_nivel,
            'codigo' => 'NIV-NEW',
            'nombre' => 'Nombre nuevo',
            'estado' => 'INACTIVO',
        ]);
    }

    public function test_nivel_destroy_se_elimina_sin_dependencias(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create();

        $this->delete(route('niveles.destroy', $nivel))->assertRedirect(route('niveles.index'));

        $this->assertDatabaseMissing('niveles', ['id_nivel' => $nivel->id_nivel]);
    }

    public function test_nivel_destroy_bloqueado_con_grados(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create();
        Grado::factory()->create(['id_nivel' => $nivel->id_nivel, 'nombre' => 'Primero', 'orden' => 1]);

        $this->from(route('niveles.index'))
            ->delete(route('niveles.destroy', $nivel))
            ->assertRedirect(route('niveles.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('niveles', ['id_nivel' => $nivel->id_nivel]);
    }

    public function test_nivel_destroy_bloqueado_con_matriculas(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create();
        Matricula::factory()->create(['id_nivel' => $nivel->id_nivel]);

        $this->from(route('niveles.index'))
            ->delete(route('niveles.destroy', $nivel))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('niveles', ['id_nivel' => $nivel->id_nivel]);
    }

    // ----------------------------------------------------------------- GRADOS

    public function test_grado_store_persiste(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create();

        $this->post(route('grados.store'), [
            'id_nivel' => $nivel->id_nivel,
            'nombre' => 'Sexto de Primaria',
            'orden' => 6,
            'estado' => 'ACTIVO',
        ])->assertRedirect(route('grados.index'));

        $this->assertDatabaseHas('grados', [
            'id_nivel' => $nivel->id_nivel,
            'nombre' => 'Sexto de Primaria',
            'orden' => 6,
        ]);
    }

    public function test_grado_rechaza_nombre_duplicado_en_el_mismo_nivel(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create();
        Grado::factory()->create(['id_nivel' => $nivel->id_nivel, 'nombre' => 'Unico', 'orden' => 1]);

        $this->post(route('grados.store'), [
            'id_nivel' => $nivel->id_nivel,
            'nombre' => 'Unico',
            'orden' => 9,
            'estado' => 'ACTIVO',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_grado_rechaza_orden_duplicado_en_el_mismo_nivel(): void
    {
        $this->admin();
        $nivel = Nivel::factory()->create();
        Grado::factory()->create(['id_nivel' => $nivel->id_nivel, 'nombre' => 'Alfa', 'orden' => 3]);

        $this->post(route('grados.store'), [
            'id_nivel' => $nivel->id_nivel,
            'nombre' => 'Beta',
            'orden' => 3,
            'estado' => 'ACTIVO',
        ])->assertSessionHasErrors('orden');
    }

    public function test_grado_destroy_bloqueado_con_alumnos(): void
    {
        $this->admin();
        $grado = Grado::factory()->create();
        Alumno::factory()->create(['id_grado' => $grado->id_grado]);

        $this->from(route('grados.index'))
            ->delete(route('grados.destroy', $grado))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('grados', ['id_grado' => $grado->id_grado]);
    }

    public function test_grado_destroy_se_elimina_sin_dependencias(): void
    {
        $this->admin();
        $grado = Grado::factory()->create();

        $this->delete(route('grados.destroy', $grado))->assertRedirect(route('grados.index'));

        $this->assertDatabaseMissing('grados', ['id_grado' => $grado->id_grado]);
    }

    // -------------------------------------------------------------- FACULTADES

    public function test_facultad_store_persiste(): void
    {
        $this->admin();

        $this->post(route('facultades.store'), [
            'nombre' => 'Facultad de Ingeniería',
            'estado' => 'ACTIVO',
        ])->assertRedirect(route('facultades.index'));

        $this->assertDatabaseHas('facultades', ['nombre' => 'Facultad de Ingeniería', 'estado' => 'ACTIVO']);
    }

    public function test_facultad_rechaza_nombre_duplicado(): void
    {
        $this->admin();
        Facultad::factory()->create(['nombre' => 'Facultad Duplicada']);

        $this->post(route('facultades.store'), [
            'nombre' => 'Facultad Duplicada',
            'estado' => 'ACTIVO',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_facultad_destroy_bloqueado_con_ciclos(): void
    {
        $this->admin();
        $facultad = Facultad::factory()->create();
        CicloAcademia::factory()->create(['id_facultad' => $facultad->id_facultad, 'nombre' => 'Ciclo de prueba', 'turno' => 'TARDE']);

        $this->from(route('facultades.index'))
            ->delete(route('facultades.destroy', $facultad))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('facultades', ['id_facultad' => $facultad->id_facultad]);
    }

    public function test_facultad_destroy_se_elimina_sin_dependencias(): void
    {
        $this->admin();
        $facultad = Facultad::factory()->create();

        $this->delete(route('facultades.destroy', $facultad))->assertRedirect(route('facultades.index'));

        $this->assertDatabaseMissing('facultades', ['id_facultad' => $facultad->id_facultad]);
    }

    // --------------------------------------------------------------- PERIODOS

    public function test_periodo_store_persiste(): void
    {
        $this->admin();

        $this->post(route('periodos.store'), [
            'codigo' => '2026-1',
            'nombre' => 'Primer semestre 2026',
            'anio' => 2026,
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-07-31',
            'estado' => 'ABIERTO',
        ])->assertRedirect(route('periodos.index'));

        $this->assertDatabaseHas('periodos_academicos', [
            'codigo' => '2026-1',
            'nombre' => 'Primer semestre 2026',
            'anio' => 2026,
            'estado' => 'ABIERTO',
        ]);
    }

    public function test_periodo_rechaza_fecha_fin_anterior_al_inicio(): void
    {
        $this->admin();

        $this->post(route('periodos.store'), [
            'codigo' => '2026-X',
            'nombre' => 'Período inválido',
            'anio' => 2026,
            'fecha_inicio' => '2026-07-31',
            'fecha_fin' => '2026-02-01',
            'estado' => 'PLANIFICADO',
        ])->assertSessionHasErrors('fecha_fin');
    }

    public function test_periodo_rechaza_codigo_duplicado(): void
    {
        $this->admin();
        PeriodoAcademico::factory()->create(['codigo' => '2025-2']);

        $this->post(route('periodos.store'), [
            'codigo' => '2025-2',
            'nombre' => 'Otro período',
            'anio' => 2025,
            'fecha_inicio' => '2025-08-01',
            'fecha_fin' => '2025-12-31',
            'estado' => 'PLANIFICADO',
        ])->assertSessionHasErrors('codigo');
    }

    public function test_periodo_destroy_bloqueado_si_esta_cerrado(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create(['estado' => 'CERRADO']);

        $this->from(route('periodos.index'))
            ->delete(route('periodos.destroy', $periodo))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('periodos_academicos', ['id_periodo' => $periodo->id_periodo]);
    }

    public function test_periodo_destroy_bloqueado_con_matriculas(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create(['estado' => 'ABIERTO']);
        Matricula::factory()->create(['id_periodo' => $periodo->id_periodo]);

        $this->from(route('periodos.index'))
            ->delete(route('periodos.destroy', $periodo))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('periodos_academicos', ['id_periodo' => $periodo->id_periodo]);
    }

    public function test_periodo_update_persiste(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create(['codigo' => '2024-1', 'estado' => 'PLANIFICADO']);

        $this->put(route('periodos.update', $periodo), [
            'codigo' => '2024-1',
            'nombre' => $periodo->nombre,
            'anio' => 2024,
            'fecha_inicio' => '2024-02-01',
            'fecha_fin' => '2024-07-31',
            'estado' => 'CERRADO',
        ])->assertRedirect(route('periodos.index'));

        $this->assertDatabaseHas('periodos_academicos', ['id_periodo' => $periodo->id_periodo, 'estado' => 'CERRADO']);
    }

    // -------------------------------------------------------------- CONCEPTOS

    public function test_concepto_store_persiste_todos_los_campos(): void
    {
        $this->admin();

        $this->post(route('conceptos.store'), [
            'codigo' => 'MEN-001',
            'nombre' => 'Mensualidad de zs',
            'tipo' => 'MENSUALIDAD',
            'modalidad_aplicable' => 'ESCOLAR',
            'monto_referencial' => '350.00',
            'estado' => 'ACTIVO',
        ])->assertRedirect(route('conceptos.index'));

        $this->assertDatabaseHas('conceptos_cobro', [
            'codigo' => 'MEN-001',
            'tipo' => 'MENSUALIDAD',
            'modalidad_aplicable' => 'ESCOLAR',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_concepto_rechaza_tipo_invalido(): void
    {
        $this->admin();

        $this->post(route('conceptos.store'), [
            'codigo' => 'TIP-001',
            'nombre' => 'Concepto con tipo inválido',
            'tipo' => 'BECA',
            'modalidad_aplicable' => 'AMBOS',
            'monto_referencial' => '10.00',
            'estado' => 'ACTIVO',
        ])->assertSessionHasErrors('tipo');
    }

    public function test_concepto_destroy_bloqueado_con_cuentas_asociadas(): void
    {
        $this->admin();
        $concepto = ConceptoCobro::factory()->create();
        \App\Models\CuentaPorCobrar::factory()->create(['id_concepto' => $concepto->id_concepto]);

        $this->from(route('conceptos.index'))
            ->delete(route('conceptos.destroy', $concepto))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('conceptos_cobro', ['id_concepto' => $concepto->id_concepto]);
    }

    public function test_concepto_destroy_se_elimina_sin_dependencias(): void
    {
        $this->admin();
        $concepto = ConceptoCobro::factory()->create();

        $this->delete(route('conceptos.destroy', $concepto))->assertRedirect(route('conceptos.index'));

        $this->assertDatabaseMissing('conceptos_cobro', ['id_concepto' => $concepto->id_concepto]);
    }

    public function test_concepto_update_persiste(): void
    {
        $this->admin();
        $concepto = ConceptoCobro::factory()->create();

        $this->put(route('conceptos.update', $concepto), [
            'codigo' => $concepto->codigo,
            'nombre' => 'Concepto renombrado',
            'tipo' => $concepto->tipo,
            'modalidad_aplicable' => 'ACADEMIA',
            'monto_referencial' => '99.50',
            'estado' => 'INACTIVO',
        ])->assertRedirect(route('conceptos.index'));

        $this->assertDatabaseHas('conceptos_cobro', [
            'id_concepto' => $concepto->id_concepto,
            'nombre' => 'Concepto renombrado',
            'modalidad_aplicable' => 'ACADEMIA',
            'estado' => 'INACTIVO',
        ]);
    }

    // ------------------------------------------------------------- CATEGORIAS

    public function test_categoria_store_persiste(): void
    {
        $this->admin();

        $this->post(route('categorias.store'), [
            'nombre' => 'Servicios básicos',
            'estado' => 'ACTIVO',
        ])->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categorias_gasto', ['nombre' => 'Servicios básicos', 'estado' => 'ACTIVO']);
    }

    public function test_categoria_rechaza_nombre_duplicado(): void
    {
        $this->admin();
        CategoriaGasto::factory()->create(['nombre' => 'Categoría repetida']);

        $this->post(route('categorias.store'), [
            'nombre' => 'Categoría repetida',
            'estado' => 'ACTIVO',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_categoria_destroy_bloqueado_con_gastos(): void
    {
        $this->admin();
        $categoria = CategoriaGasto::factory()->create();
        \App\Models\Gasto::factory()->create(['id_categoria_gasto' => $categoria->id_categoria_gasto]);

        $this->from(route('categorias.index'))
            ->delete(route('categorias.destroy', $categoria))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categorias_gasto', ['id_categoria_gasto' => $categoria->id_categoria_gasto]);
    }

    public function test_categoria_destroy_se_elimina_sin_dependencias(): void
    {
        $this->admin();
        $categoria = CategoriaGasto::factory()->create();

        $this->delete(route('categorias.destroy', $categoria))->assertRedirect(route('categorias.index'));

        $this->assertDatabaseMissing('categorias_gasto', ['id_categoria_gasto' => $categoria->id_categoria_gasto]);
    }

    // ----------------------------------------------------------------- CICLOS

    public function test_ciclo_store_persiste(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create();
        $facultad = Facultad::factory()->create();

        $this->post(route('ciclos.store'), [
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo de Programación',
            'turno' => 'NOCHE',
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-08-31',
            'monto_referencial' => '1200.00',
            'vacantes' => 30,
            'estado' => 'ABIERTO',
        ])->assertRedirect(route('ciclos.index'));

        $this->assertDatabaseHas('ciclos_academia', [
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo de Programación',
            'turno' => 'NOCHE',
            'vacantes' => 30,
            'estado' => 'ABIERTO',
        ]);
    }

    public function test_ciclo_rechaza_combinacion_duplicada(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create();
        $facultad = Facultad::factory()->create();
        CicloAcademia::factory()->create([
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo Único',
            'turno' => 'TARDE',
        ]);

        $this->post(route('ciclos.store'), [
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo Único',
            'turno' => 'TARDE',
            'estado' => 'PLANIFICADO',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_ciclo_permite_mismo_nombre_en_otro_turno(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create();
        $facultad = Facultad::factory()->create();
        CicloAcademia::factory()->create([
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo Mañana',
            'turno' => 'MANANA',
        ]);

        $this->post(route('ciclos.store'), [
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo Mañana',
            'turno' => 'TARDE',
            'estado' => 'PLANIFICADO',
        ])->assertRedirect(route('ciclos.index'));

        $this->assertDatabaseHas('ciclos_academia', [
            'id_periodo' => $periodo->id_periodo,
            'nombre' => 'Ciclo Mañana',
            'turno' => 'TARDE',
        ]);
    }

    public function test_ciclo_rechaza_turno_invalido(): void
    {
        $this->admin();
        $periodo = PeriodoAcademico::factory()->create();
        $facultad = Facultad::factory()->create();

        $this->post(route('ciclos.store'), [
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo con turno inválido',
            'turno' => 'MADRUGADA',
            'estado' => 'PLANIFICADO',
        ])->assertSessionHasErrors('turno');
    }

    public function test_ciclo_destroy_bloqueado_con_matriculas(): void
    {
        $this->admin();
        $ciclo = CicloAcademia::factory()->create(['estado' => 'ABIERTO', 'nombre' => 'Ciclo con matrícula']);
        // La FK fk_matriculas_ciclo_periodo es compuesta: id_ciclo e
        // id_periodo deben pertenecer al mismo ciclo.
        Matricula::factory()->create([
            'modalidad' => 'ACADEMIA',
            'id_ciclo' => $ciclo->id_ciclo,
            'id_periodo' => $ciclo->id_periodo,
            'id_grado' => null,
        ]);

        $this->from(route('ciclos.index'))
            ->delete(route('ciclos.destroy', $ciclo))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('ciclos_academia', ['id_ciclo' => $ciclo->id_ciclo]);
    }

    public function test_ciclo_destroy_se_elimina_sin_dependencias(): void
    {
        $this->admin();
        $ciclo = CicloAcademia::factory()->create(['estado' => 'PLANIFICADO']);

        $this->delete(route('ciclos.destroy', $ciclo))->assertRedirect(route('ciclos.index'));

        $this->assertDatabaseMissing('ciclos_academia', ['id_ciclo' => $ciclo->id_ciclo]);
    }

    // ------------------------------------------------------ PAGINAS Y ROLES

    #[DataProvider('paginasDeCatalogo')]
    public function test_las_paginas_de_catalogo_responden(string $ruta): void
    {
        $this->admin();

        $this->get($ruta)->assertOk();
    }

    public static function paginasDeCatalogo(): array
    {
        return [
            'niveles' => ['/niveles'],
            'niveles create' => ['/niveles/create'],
            'grados' => ['/grados'],
            'grados create' => ['/grados/create'],
            'facultades' => ['/facultades'],
            'facultades create' => ['/facultades/create'],
            'periodos' => ['/periodos'],
            'periodos create' => ['/periodos/create'],
            'conceptos' => ['/conceptos'],
            'conceptos create' => ['/conceptos/create'],
            'categorias' => ['/categorias'],
            'categorias create' => ['/categorias/create'],
            'ciclos' => ['/ciclos'],
            'ciclos create' => ['/ciclos/create'],
        ];
    }

    public function test_las_paginas_de_edicion_responden(): void
    {
        $this->admin();

        $nivel = Nivel::factory()->create();
        $grado = Grado::factory()->create(['nombre' => 'Editable', 'orden' => 5]);
        $facultad = Facultad::factory()->create();
        $periodo = PeriodoAcademico::factory()->create();
        $concepto = ConceptoCobro::factory()->create();
        $categoria = CategoriaGasto::factory()->create();
        $ciclo = CicloAcademia::factory()->create();

        $this->get(route('niveles.edit', $nivel))->assertOk();
        $this->get(route('grados.edit', $grado))->assertOk();
        $this->get(route('facultades.edit', $facultad))->assertOk();
        $this->get(route('periodos.edit', $periodo))->assertOk();
        $this->get(route('conceptos.edit', $concepto))->assertOk();
        $this->get(route('categorias.edit', $categoria))->assertOk();
        $this->get(route('ciclos.edit', $ciclo))->assertOk();
    }

    public function test_el_cajero_no_puede_administrar_los_catalogos(): void
    {
        $this->actingAs(User::factory()->cajero()->create());

        $nivel = Nivel::factory()->create();

        $this->get('/niveles')->assertForbidden();
        $this->get('/niveles/create')->assertForbidden();
        $this->post(route('niveles.store'), [
            'codigo' => 'CAJ-01',
            'nombre' => 'Intento no permitido',
            'estado' => 'ACTIVO',
        ])->assertForbidden();
        $this->delete(route('niveles.destroy', $nivel))->assertForbidden();

        $this->assertDatabaseMissing('niveles', ['nombre' => 'Intento no permitido']);
    }

    public function test_el_cajero_no_puede_crear_grados_ni_ciclos(): void
    {
        $this->actingAs(User::factory()->cajero()->create());

        $nivel = Nivel::factory()->create();
        $periodo = PeriodoAcademico::factory()->create();
        $facultad = Facultad::factory()->create();

        $this->post(route('grados.store'), [
            'id_nivel' => $nivel->id_nivel,
            'nombre' => 'Grado no permitido',
            'orden' => 2,
            'estado' => 'ACTIVO',
        ])->assertForbidden();

        $this->post(route('ciclos.store'), [
            'id_periodo' => $periodo->id_periodo,
            'id_facultad' => $facultad->id_facultad,
            'nombre' => 'Ciclo no permitido',
            'turno' => 'TARDE',
            'estado' => 'PLANIFICADO',
        ])->assertForbidden();

        $this->assertDatabaseMissing('grados', ['nombre' => 'Grado no permitido']);
        $this->assertDatabaseMissing('ciclos_academia', ['nombre' => 'Ciclo no permitido']);
    }

    public function test_la_secretaria_puede_administrar_los_catalogos(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretaria']));

        $this->get('/niveles')->assertOk();
        $this->get('/ciclos')->assertOk();
        $this->get('/conceptos')->assertOk();
    }
}
