<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginDesignTest extends TestCase
{
    use RefreshDatabase;

    private function login(): string
    {
        return $this->get('/login')->assertOk()->getContent();
    }

    public function test_el_panel_izquierdo_conserva_la_identidad_institucional(): void
    {
        $html = $this->login();

        $this->assertStringContainsString('login-brand-panel', $html, 'falta el panel izquierdo');
        $this->assertStringContainsString('Next Level', $html);
        $this->assertStringContainsString('School', $html);
        $this->assertStringContainsString('Educación en el próximo nivel', $html);
        $this->assertStringContainsString('Educación que impulsa', $html);
        $this->assertStringContainsString('el <span class="brand-accent">futuro.</span>', $html,
            'la palabra "futuro." debe llevar el acento rojo');
        $this->assertStringContainsString(
            'Gestiona matrículas, pagos, estudiantes y procesos académicos desde un solo lugar.',
            $html
        );
        $this->assertStringContainsString('Plataforma administrativa institucional', $html);
        $this->assertStringContainsString(date('Y') . ' Next Level School', $html, 'falta el copyright');
    }

    public function test_la_fila_de_funcionalidades_muestra_los_cuatro_modulos(): void
    {
        $html = $this->login();

        foreach (['Matrículas', 'Pagos', 'Gestión académica', 'Reportes'] as $modulo) {
            $this->assertStringContainsString($modulo, $html, "falta el modulo {$modulo}");
        }

        $this->assertSame(4, substr_count($html, 'class="brand-feature"'),
            'deben ser cuatro elementos discretos, ni mas ni menos');
    }

    public function test_el_panel_derecho_mantiene_la_jerarquia_del_formulario(): void
    {
        $html = $this->login();

        $this->assertStringContainsString('login-form-panel', $html, 'falta el panel derecho');
        $this->assertStringContainsString('Bienvenido de nuevo', $html);
        $this->assertStringContainsString('Inicia sesión', $html);
        $this->assertStringContainsString('Ingresa tus credenciales para acceder al sistema.', $html);
    }

    public function test_los_campos_conservan_su_nombre_y_placeholder(): void
    {
        $html = $this->login();

        $this->assertMatchesRegularExpression('/<input[^>]*name="email"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="password"/', $html);
        $this->assertStringContainsString('placeholder="usuario@nextlevel.edu.pe"', $html);
        $this->assertStringContainsString('placeholder="Ingresa tu contraseña"', $html);
        $this->assertStringContainsString('Correo electrónico', $html);
        $this->assertStringContainsString('Contraseña', $html);
        $this->assertStringContainsString('fa-envelope', $html, 'falta el icono de correo');
        $this->assertStringContainsString('fa-lock', $html, 'falta el icono de candado');
    }

    public function test_se_conservan_el_toggle_el_recuerdame_y_la_recuperacion(): void
    {
        $html = $this->login();

        $this->assertStringContainsString('x-bind:type="showPassword ? \'text\' : \'password\'"', $html,
            'el campo de contrasena debe seguir alternando tipo');
        $this->assertStringContainsString('x-on:click="showPassword = !showPassword"', $html,
            'debe seguir existiendo el boton mostrar/ocultar');
        $this->assertStringContainsString('name="remember"', $html, 'falta el checkbox Recuerdame');
        $this->assertStringContainsString('Recuérdame', $html);
        $this->assertStringContainsString(route('password.request'), $html,
            'debe seguir el enlace de recuperacion de contrasena');
        $this->assertStringContainsString('¿Olvidaste tu contraseña?', $html);
    }

    public function test_el_boton_ingresar_conserva_la_ruta_de_login(): void
    {
        $html = $this->login();

        $this->assertStringContainsString('action="' . route('login') . '"', $html);
        $this->assertStringContainsString('Ingresar', $html, 'el boton debe tener etiqueta visible');
        $this->assertStringContainsString('fa-arrow-right', $html, 'el boton debe llevar la flecha');
        $this->assertStringContainsString('btn-login', $html);
    }

    public function test_se_conservan_el_divisor_y_los_avisos_de_seguridad(): void
    {
        $html = $this->login();

        $this->assertStringContainsString('login-divider', $html);
        $this->assertStringContainsString('Acceso seguro para personal autorizado', $html);
        $this->assertStringContainsString('¿No tienes cuenta? Solicítala al administrador del sistema.', $html);
    }

    public function test_los_errores_y_el_estado_de_sesion_siguen_visible(): void
    {
        $html = $this->followingRedirects()
            ->from('/login')
            ->post('/login', ['email' => 'no-es-un-correo', 'password' => ''])
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('login-alert', $html, 'debe quedar el bloque de error/estado');
        $this->assertStringContainsString('text-red-700', $html, 'el error debe seguir en rojo');

        $conEstado = $this->withSession(['status' => 'Mensaje de prueba'])
            ->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString('Mensaje de prueba', $conEstado);
        $this->assertStringContainsString('text-amber-800', $conEstado);
    }

    public function test_el_diseno_usa_la_paleta_institucional(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('--nl-brand-navy: #002653', $css, 'falta el azul institucional del logo');
        $this->assertStringContainsString('--nl-accent-red: #fb000f', $css, 'falta el rojo del logo');

        $html = $this->login();
        $this->assertStringNotContainsString('Regístrate', $html);
        $this->assertStringNotContainsString('route(\'register\')', $html);
    }

    public function test_la_pantalla_de_recuperacion_conserva_su_logica_y_estilo(): void
    {
        $html = $this->get('/forgot-password')->assertOk()->getContent();

        $this->assertStringContainsString('action="' . route('password.email') . '"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*name="email"/', $html);
        $this->assertStringContainsString('placeholder="usuario@nextlevel.edu.pe"', $html);
        $this->assertStringContainsString('¿Olvidaste tu contraseña?', $html);
        $this->assertStringContainsString('Volver al inicio de sesión', $html);
        $this->assertStringContainsString('login-layout is-single', $html, 'debe usar la tarjeta de una columna');
        $this->assertStringNotContainsString('Forgot your password', $html, 'no debe quedar texto en ingles');
    }

    public function test_la_pantalla_de_nueva_clave_conserva_token_y_campos(): void
    {
        $token = 'token-de-prueba-123';

        $html = $this->get('/reset-password/' . $token . '?email=admin@nextlevel.edu.pe')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('action="' . route('password.reset.update') . '"', $html);
        $this->assertStringContainsString('value="' . $token . '"', $html, 'debe viajar el token');
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="password_confirmation"', $html);
        $this->assertStringContainsString('admin@nextlevel.edu.pe', $html, 'debe conservar el correo recibido');
        $this->assertStringContainsString('login-layout is-single', $html);
    }
}
