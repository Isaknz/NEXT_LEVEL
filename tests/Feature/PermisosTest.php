<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Permisos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Verifica que la matriz de permisos sea la misma para el middleware, para el
 * menú lateral y para las respuestas HTTP reales.
 */
class PermisosTest extends TestCase
{
    use RefreshDatabase;

    public static function rutasPorRol(): array
    {
        return [
            // ADMIN: acceso total
            ['admin', 'dashboard', true],
            ['admin', 'alumnos.index', true],
            ['admin', 'cuentas-por-cobrar.anular', true],
            ['admin', 'cajas.cerrar', true],
            ['admin', 'users.destroy', true],

            // GERENTE: todo, pero en Usuarios solo puede consultar
            ['gerente', 'dashboard', true],
            ['gerente', 'cuentas-por-cobrar.anular', true],
            ['gerente', 'cajas.cerrar', true],
            ['gerente', 'auditoria.index', true],
            ['gerente', 'users.index', true],
            ['gerente', 'users.show', true],
            ['gerente', 'users.create', false],
            ['gerente', 'users.store', false],
            ['gerente', 'users.edit', false],
            ['gerente', 'users.update', false],
            ['gerente', 'users.destroy', false],

            // SECRETARIA: catálogos y cuentas, sin anular
            ['secretaria', 'alumnos.index', true],
            ['secretaria', 'cuentas-por-cobrar.index', true],
            ['secretaria', 'cuentas-por-cobrar.store', true],
            ['secretaria', 'cajas.cerrar', true],
            ['secretaria', 'cuentas-por-cobrar.anular', false],
            ['secretaria', 'pagos.anular', false],
            ['secretaria', 'gastos.anular', false],
            ['secretaria', 'usuarios.index', false],
            ['secretaria', 'reportes.index', false],

            // CAJERO: solo registro de pagos y gastos
            ['cajero', 'pagos.create', true],
            ['cajero', 'pagos.store', true],
            ['cajero', 'gastos.create', true],
            ['cajero', 'gastos.store', true],
            ['cajero', 'cajas.index', true],
            ['cajero', 'cajas.show', true],
            ['cajero', 'cajas.create', false],
            ['cajero', 'cajas.cerrar', false],
            ['cajero', 'cuentas-por-cobrar.index', false],
            ['cajero', 'alumnos.index', false],
            ['cajero', 'niveles.index', false],

            // Rol desconocido o ruta sin nombre
            ['desconocido', 'dashboard', false],
            ['admin', '', false],
            ['admin', null, false],
        ];
    }

    #[DataProvider('rutasPorRol')]
    public function test_la_matriz_de_permisos(?string $rol, ?string $ruta, bool $permitido): void
    {
        $this->assertSame(
            $permitido,
            Permisos::permite($rol, $ruta),
            "El rol '{$rol}' sobre '{$ruta}' debía estar " . ($permitido ? 'permitido' : 'denegado')
        );
    }

    public function test_el_middleware_responde_403_a_un_cajero_en_catalogos(): void
    {
        $this->actingAs(User::factory()->cajero()->create());

        $this->get('/niveles')->assertForbidden();
        $this->get('/cuentas-por-cobrar')->assertForbidden();
        $this->get('/reportes')->assertForbidden();
    }

    public function test_el_middleware_permite_las_rutas_del_cajero(): void
    {
        $this->actingAs(User::factory()->cajero()->create());

        $this->get('/cajas')->assertOk();
        $this->get('/pagos/create')->assertOk();
        $this->get('/gastos/create')->assertOk();
    }

    public function test_la_secretaria_no_llega_a_usuarios_ni_reportes(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretaria']));

        $this->get('/users')->assertForbidden();
        $this->get('/reportes')->assertForbidden();
    }

    public function test_el_gerente_solo_consulta_usuarios_y_el_admin_los_gestiona(): void
    {
        $gerente = User::factory()->gerente()->create();
        $this->actingAs($gerente);

        // Consulta permitida.
        $this->get('/users')->assertOk();
        $this->get('/users/'.User::factory()->create()->id)->assertOk();

        // Crear, editar y eliminar prohibidos.
        $this->get('/users/create')->assertForbidden();
        $this->post('/users', [])->assertForbidden();
        $this->get('/users/'.$gerente->id.'/edit')->assertForbidden();
        $this->put('/users/'.$gerente->id, [])->assertForbidden();
        $this->delete('/users/'.User::factory()->create()->id)->assertForbidden();

        // El gerente no puede auto-promoverse a administrador.
        $this->put('/users/'.$gerente->id, [
            'nombre' => 'Gerente Auto',
            'email' => $gerente->email,
            'role' => 'admin',
        ])->assertForbidden();
        $this->assertSame('gerente', $gerente->fresh()->role);

        // El administrador sí puede hacer las cuatro cosas.
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/users/create')->assertOk();
        $this->delete('/users/'.$gerente->id)->assertRedirect(route('users.index'));
    }

    public function test_sin_sesion_las_rutas_protegidas_redirigen_al_login(): void
    {
        $this->get('/cuentas-por-cobrar')->assertRedirect('/login');
    }

    public function test_el_gerente_ve_la_lista_de_usuarios_sin_botones_de_gestion(): void
    {
        User::factory()->create(['nombre' => 'Usuario Visible']);

        $this->actingAs(User::factory()->gerente()->create());
        $html = $this->get('/users')->assertOk()->getContent();

        $this->assertStringContainsString('Usuario Visible', $html, 'el gerente si puede consultar');
        $this->assertStringNotContainsString(route('users.create'), $html, 'no debe ofrecer crear');
        $this->assertStringNotContainsString('fa-edit', $html, 'no debe ofrecer editar');
        $this->assertStringNotContainsString('fa-trash', $html, 'no debe ofrecer eliminar');
        $this->assertStringContainsString('Solo consulta', $html);

        // El administrador si ve los botones.
        $this->actingAs(User::factory()->admin()->create());
        $htmlAdmin = $this->get('/users')->assertOk()->getContent();
        $this->assertStringContainsString(route('users.create'), $htmlAdmin);
        $this->assertStringContainsString('fa-trash', $htmlAdmin);
    }

    public function test_el_menu_oculta_los_enlaces_no_permitidos(): void
    {
        $this->actingAs(User::factory()->cajero()->create());

        $html = $this->get('/cajas')->assertOk()->getContent();

        $this->assertStringContainsString(route('cajas.index'), $html, 'el cajero debe ver el enlace a cajas');
        $this->assertStringNotContainsString(route('cuentas-por-cobrar.index'), $html, 'el cajero no debe ver el enlace a cuentas por cobrar');
        $this->assertStringNotContainsString(route('niveles.index'), $html, 'el cajero no debe ver el enlace a niveles');
    }

    public function test_el_menu_muestra_cuentas_a_la_secretaria(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'secretaria']));

        $html = $this->get('/cuentas-por-cobrar')->assertOk()->getContent();

        $this->assertStringContainsString(route('cuentas-por-cobrar.index'), $html);
        $this->assertStringNotContainsString(route('users.index'), $html, 'la secretaría no debe ver el enlace a usuarios');
    }
}
