<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\Pago;
use App\Models\Caja;
use App\Models\Matricula;
use App\Models\Apoderado;

class AlumnoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_alumno(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nivel = Nivel::factory()->create();
        $grado = Grado::factory()->create(['id_nivel' => $nivel->id_nivel]);

        $response = $this->post('/alumnos', [
            'codigo' => 'AL-TEST-001',
            'dni' => '12345678',
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'fecha_nacimiento' => '2000-01-01',
            'sexo' => 'M',
            'celular' => '999999999',
            'email' => 'juan@test.com',
            'id_grado' => $grado->id_grado,
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('alumnos', [
            'codigo' => 'AL-TEST-001',
            'nombres' => 'Juan',
        ]);
    }

    public function test_can_edit_alumno(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nivel = Nivel::factory()->create();
        $grado = Grado::factory()->create(['id_nivel' => $nivel->id_nivel]);
        $alumno = Alumno::factory()->create([
            'nombres' => 'Pedro',
            'apellidos' => 'Garcia',
            'id_grado' => $grado->id_grado,
        ]);

        $response = $this->put('/alumnos/' . $alumno->id_alumno, [
            'codigo' => $alumno->codigo,
            'dni' => '87654321',
            'nombres' => 'Pedro Updated',
            'apellidos' => 'Garcia Updated',
            'fecha_nacimiento' => '2000-01-01',
            'sexo' => 'M',
            'id_grado' => $grado->id_grado,
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $alumno->id_alumno,
            'nombres' => 'Pedro Updated',
        ]);
    }

    public function test_can_delete_alumno(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $alumno = Alumno::factory()->create();

        $response = $this->delete('/alumnos/' . $alumno->id_alumno);

        $response->assertRedirect();
        $this->assertSoftDeleted('alumnos', [
            'id_alumno' => $alumno->id_alumno,
        ]);
    }

    public function test_secretaria_cannot_annul_pago(): void
    {
        $user = User::factory()->create(['role' => 'secretaria']);
        $this->actingAs($user);

        $pago = Pago::create([
            'codigo' => 'PAG-SEC-001',
            'id_matricula' => Matricula::factory()->create()->id_matricula,
            'id_caja' => Caja::factory()->create()->id_caja,
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'monto_total' => 100,
            'estado' => 'CONFIRMADO',
            'registrado_por' => $user->id,
        ]);

        $response = $this->post("/pagos/{$pago->id_pago}/anular", [
            'motivo_anulacion' => 'Test',
        ]);

        $response->assertForbidden();
        $this->assertSame('CONFIRMADO', $pago->fresh()->estado, 'El pago no debe anularse.');
    }

    public function test_al_crear_alumno_se_vincula_el_apoderado_en_el_pivot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $apoderado = Apoderado::create([
            'dni' => '11223344',
            'nombres' => 'Maria',
            'apellidos' => 'Lopez',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->post('/alumnos', [
            'nombres' => 'Ana',
            'apellidos' => 'Lopez',
            'id_apoderado' => $apoderado->id_apoderado,
            'parentesco' => 'MADRE',
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect();

        $alumno = Alumno::where('nombres', 'Ana')->firstOrFail();
        $this->assertDatabaseHas('alumno_apoderado', [
            'id_alumno' => $alumno->id_alumno,
            'id_apoderado' => $apoderado->id_apoderado,
            'parentesco' => 'MADRE',
            'es_principal' => 1,
        ]);
        $this->assertSame(1, $apoderado->alumnos()->count());
    }

    public function test_al_actualizar_alumno_se_sincroniza_el_apoderado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $alumno = Alumno::factory()->create();

        $apoderado = Apoderado::create([
            'dni' => '55667788',
            'nombres' => 'Jose',
            'apellidos' => 'Ramos',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->put('/alumnos/'.$alumno->id_alumno, [
            'codigo' => $alumno->codigo,
            'nombres' => $alumno->nombres,
            'apellidos' => $alumno->apellidos,
            'id_apoderado' => $apoderado->id_apoderado,
            'parentesco' => 'PADRE',
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('alumno_apoderado', [
            'id_alumno' => $alumno->id_alumno,
            'id_apoderado' => $apoderado->id_apoderado,
            'parentesco' => 'PADRE',
        ]);
    }

    public function test_un_codigo_eliminado_logicamente_no_provoca_error_500(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Alumno::factory()->create(['codigo' => 'DUP-001'])->delete();

        $response = $this->post('/alumnos', [
            'codigo' => 'DUP-001',
            'nombres' => 'Nuevo',
            'apellidos' => 'Alumno',
            'estado' => 'ACTIVO',
        ]);

        $response->assertSessionHasErrors('codigo');
        $response->assertStatus(302);
    }
}
