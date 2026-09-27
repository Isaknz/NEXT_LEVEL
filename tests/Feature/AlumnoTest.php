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
}
