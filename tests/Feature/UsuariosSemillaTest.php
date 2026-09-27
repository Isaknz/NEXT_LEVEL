<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsuariosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Verifica las cuentas que crea UsuariosSeeder y que el acceso real por HTTP
 * respeta el rol de cada una, incluida la clave temporal.
 */
class UsuariosSemillaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsuariosSeeder::class);
    }

    public function test_se_crean_las_tres_cuentas_solicitadas(): void
    {
        $admin = User::where('email', 'admin@nextlevel.edu.pe')->first();
        $gerente = User::where('email', 'gerente@nextlevel.edu.pe')->first();
        $secretaria = User::where('email', 'secretaria@nextlevel.edu.pe')->first();

        $this->assertNotNull($admin, 'debe existir la cuenta de administrador');
        $this->assertNotNull($gerente, 'debe existir la cuenta de gerente');
        $this->assertNotNull($secretaria, 'debe existir la cuenta de secretaria');

        $this->assertSame('admin', $admin->role);
        $this->assertSame('gerente', $gerente->role);
        $this->assertSame('secretaria', $secretaria->role);

        foreach ([$admin, $gerente, $secretaria] as $usuario) {
            $this->assertSame('activo', $usuario->estado);
            $this->assertNull($usuario->password_changed_at, 'deben arrancar con clave temporal');
        }

        $this->assertTrue(Hash::check('senati2026', $admin->password));
        $this->assertTrue(Hash::check('nextlevel2026', $gerente->password));
        $this->assertTrue(Hash::check('nextlevel2026', $secretaria->password));
    }

    public function test_el_seeder_no_duplica_ni_pisa_una_clave_ya_cambiada(): void
    {
        $gerente = User::where('email', 'gerente@nextlevel.edu.pe')->first();
        $gerente->update(['password' => Hash::make('clave-propia'), 'password_changed_at' => now()]);

        $this->seed(UsuariosSeeder::class);

        $this->assertSame(3, User::count());
        $gerente->refresh();
        $this->assertTrue(Hash::check('clave-propia', $gerente->password), 'no debe resetear la clave ya cambiada');
        $this->assertNotNull($gerente->password_changed_at);
    }

    public function test_la_clave_temporal_bloquea_el_uso_a_traves_de_http(): void
    {
        $usuario = User::factory()->create(['password_changed_at' => null]);

        // Login real con la clave temporal, no actingAs.
        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ])->assertRedirect(route('password.change'));

        $this->get('/dashboard')->assertRedirect(route('password.change'));
        $this->get('/cajas')->assertRedirect(route('password.change'));
        $this->get('/cuentas-por-cobrar')->assertRedirect(route('password.change'));

        // Cambiar la clave habilita el sistema. updatePassword responde con
        // back(), es decir, devuelve al usuario a donde venía.
        $this->put('/password/change', [
            'password_current' => 'password',
            'password' => 'Nueva-Clave-2026',
            'password_confirmation' => 'Nueva-Clave-2026',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNotNull($usuario->fresh()->password_changed_at);
        $this->assertTrue(Hash::check('Nueva-Clave-2026', $usuario->fresh()->password));

        $this->get('/dashboard')->assertOk();
        $this->get('/cuentas-por-cobrar')->assertOk();

        // Y con la clave nueva ya puede volver a entrar por el login.
        $this->post('/logout');
        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'Nueva-Clave-2026',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_el_admin_llega_a_todo_incluidos_usuarios_y_auditoria(): void
    {
        $this->actingAs(User::factory()->admin()->create(['password_changed_at' => now()]));

        $this->get('/dashboard')->assertOk();
        $this->get('/users')->assertOk();
        $this->get('/users/create')->assertOk();
        $this->get('/auditoria')->assertOk();
        $this->get('/cuentas-por-cobrar')->assertOk();
        $this->get('/cajas')->assertOk();
        $this->get('/reportes')->assertOk();
    }

    public function test_el_gerente_opera_todo_pero_en_usuarios_solo_consulta(): void
    {
        $gerente = User::factory()->gerente()->create(['password_changed_at' => now()]);

        $this->actingAs($gerente);
        $this->get('/dashboard')->assertOk();
        $this->get('/cuentas-por-cobrar')->assertOk();
        $this->get('/cajas')->assertOk();

        // Consulta sí, gestión no.
        $this->get('/users')->assertOk();
        $this->get('/users/create')->assertForbidden();
        $this->get('/users/'.$gerente->id.'/edit')->assertForbidden();
        $this->delete('/users/'.User::factory()->create()->id)->assertForbidden();
    }

    public function test_la_secretaria_opera_lo_suyo_pero_no_usuarios_ni_reportes(): void
    {
        $this->actingAs(User::factory()->create(['password_changed_at' => now()]));

        $this->get('/dashboard')->assertOk();
        $this->get('/alumnos')->assertOk();
        $this->get('/cuentas-por-cobrar')->assertOk();
        $this->get('/cajas')->assertOk();

        $this->get('/users')->assertForbidden();
        $this->get('/users/create')->assertForbidden();
        $this->get('/auditoria')->assertForbidden();
        $this->get('/reportes')->assertForbidden();
    }
}
