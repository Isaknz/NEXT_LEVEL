<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El registro publico de Breeze permitiria que cualquiera se cree una cuenta
 * con rol 'secretaria' (el default del enum) y acceda a alumnos, matriculas,
 * pagos, cuentas por cobrar y cajas. Este test documenta el cierre.
 */
class RegistroPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_registro_publico_no_esta_disponible(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'nombre' => 'Intruso',
            'email' => 'intruso@exemplo.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruso@exemplo.com']);
    }

    public function test_la_ruta_de_registro_publico_no_existe(): void
    {
        $this->assertFalse(
            \Illuminate\Support\Facades\Route::has('register'),
            'la ruta register de Breeze no debe existir en un sistema de acceso cerrado'
        );
    }

    public function test_la_pantalla_de_login_no_ofrece_registro(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringNotContainsString('/register', $html, 'el login no debe enlazar al registro público');
        $this->assertStringNotContainsString('Regístrate', $html);
        $this->assertStringNotContainsString('Reg&iacute;strate', $html);
        $this->assertStringContainsString('Acceso seguro para personal autorizado', $html);
    }

    public function test_las_cuentas_solo_las_gestiona_el_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretaria']));
        $this->get('/users/create')->assertForbidden();

        $gerente = User::factory()->gerente()->create();
        $this->actingAs($gerente);
        $this->get('/users/create')->assertForbidden();
        $this->delete('/users/'.User::factory()->create()->id)->assertForbidden();

        $this->actingAs(User::factory()->admin()->create());
        $this->get('/users/create')->assertOk();
        $this->delete('/users/'.$gerente->id)->assertRedirect(route('users.index'));
    }

    public function test_la_clave_temporal_obliga_a_cambiarla_de_verdad(): void
    {
        $usuario = User::factory()->create([
            'role' => 'admin',
            'password_changed_at' => null,
        ]);

        // Con clave temporal no debe poder operar, solo cambiar la clave.
        $this->actingAs($usuario);

        $this->get('/dashboard')->assertRedirect(route('password.change'));
        $this->get('/cuentas-por-cobrar')->assertRedirect(route('password.change'));
        $this->get('/cajas')->assertRedirect(route('password.change'));

        // La propia pantalla de cambio de clave sí debe funcionar.
        $this->get('/password/change')->assertOk();

        $this->put('/password/change', [
            'password_current' => 'password',
            'password' => 'NuevaClave2026!',
            'password_confirmation' => 'NuevaClave2026!',
        ])->assertSessionHasNoErrors();

        $usuario->refresh();
        $this->assertNotNull($usuario->password_changed_at);

        // Con la clave ya cambiada entra con normalidad.
        $this->get('/dashboard')->assertOk();
    }
}
