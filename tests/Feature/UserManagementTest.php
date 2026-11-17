<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_un_admin_puede_crear_un_cajero(): void
    {
        $this->admin();

        $this->post(route('users.store'), [
            'nombre' => 'Cajero de Prueba',
            'email' => 'cajero@example.com',
            'password' => 'clave1234',
            'password_confirmation' => 'clave1234',
            'role' => 'cajero',
            'estado' => 'activo',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'cajero@example.com',
            'role' => 'cajero',
        ]);
    }

    public function test_el_formulario_ofrece_el_rol_cajero(): void
    {
        $this->admin();

        $this->get(route('users.create'))
            ->assertOk()
            ->assertSee('Cajero');
    }

    public function test_no_se_puede_degradar_al_ultimo_admin_activo(): void
    {
        $admin = $this->admin();

        $this->from(route('users.index'))
            ->put(route('users.update', $admin), [
                'nombre' => $admin->nombre,
                'email' => $admin->email,
                'role' => 'secretaria',
                'estado' => 'activo',
            ])
            ->assertSessionHas('error');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_no_se_puede_desactivar_al_ultimo_admin_activo(): void
    {
        $admin = $this->admin();

        $this->from(route('users.index'))
            ->put(route('users.update', $admin), [
                'nombre' => $admin->nombre,
                'email' => $admin->email,
                'role' => 'admin',
                'estado' => 'inactivo',
            ])
            ->assertSessionHas('error');

        $this->assertSame('activo', $admin->fresh()->estado);
    }

    public function test_se_puede_degradar_un_admin_si_queda_otro_activo(): void
    {
        $this->admin();
        $otroAdmin = User::factory()->admin()->create();

        $this->put(route('users.update', $otroAdmin), [
            'nombre' => $otroAdmin->nombre,
            'email' => $otroAdmin->email,
            'role' => 'secretaria',
            'estado' => 'activo',
        ])->assertRedirect(route('users.index'));

        $this->assertSame('secretaria', $otroAdmin->fresh()->role);
    }

    public function test_no_se_puede_eliminar_al_ultimo_admin_activo(): void
    {
        // Un administrador inactivo conserva la sesión y no cuenta como
        // administrador activo disponible.
        $this->actingAs(User::factory()->admin()->create(['estado' => 'inactivo']));
        $adminActivo = User::factory()->admin()->create(['estado' => 'activo']);

        $this->from(route('users.index'))
            ->delete(route('users.destroy', $adminActivo))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $adminActivo->id]);
    }
}
