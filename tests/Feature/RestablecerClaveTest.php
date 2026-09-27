<?php

namespace Tests\Feature;

use App\Models\RegistroMovimiento;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * El flujo de "¿olvvidaste tu contraseña?" debe funcionar para cualquier
 * persona del personal, no solo para el administrador, sin convertirse en una
 * vía para enumerar cuentas ni para dejar cambios de clave sin auditar.
 */
class RestablecerClaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_cualquier_rol_puede_solicitar_el_enlace_desde_el_login(): void
    {
        Notification::fake();

        $this->get('/login')->assertOk();

        // El enlace existe y lleva a un formulario utilizable.
        $this->get('/forgot-password')->assertOk();
        $this->assertTrue(view()->exists('auth.forgot-password'));
    }

    public function test_el_enlace_se_envia_al_correo_del_usuario(): void
    {
        Notification::fake();

        $secretaria = User::factory()->create([
            'email' => 'secretaria@nextlevel.edu.pe',
            'role' => 'secretaria',
        ]);

        $this->post('/forgot-password', ['email' => 'secretaria@nextlevel.edu.pe'])
            ->assertSessionHas('status');

        Notification::assertSentTo($secretaria, ResetPassword::class, function ($notification) {
            return $notification->token !== null && $notification->token !== '';
        });
    }

    public function test_con_el_enlace_se_establece_una_clave_nueva_y_se_puede_entrar(): void
    {
        Notification::fake();

        $gerente = User::factory()->gerente()->create([
            'email' => 'gerente@nextlevel.edu.pe',
            'password' => Hash::make('nextlevel2026'),
            'password_changed_at' => null,
        ]);

        $this->post('/forgot-password', ['email' => 'gerente@nextlevel.edu.pe']);

        $token = null;
        Notification::assertSentTo($gerente, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->get('/reset-password/'.$token)->assertOk();

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'gerente@nextlevel.edu.pe',
            'password' => 'Clave-Nueva-2026',
            'password_confirmation' => 'Clave-Nueva-2026',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Clave-Nueva-2026', $gerente->fresh()->password));
        $this->assertFalse(Hash::check('nextlevel2026', $gerente->fresh()->password));

        // Y entra al sistema con la nueva, sin quedar atrapado en el cambio
        // de clave temporal.
        $this->post('/login', [
            'email' => 'gerente@nextlevel.edu.pe',
            'password' => 'Clave-Nueva-2026',
        ])->assertRedirect(route('dashboard'));

        $this->get('/dashboard')->assertOk();
    }

    public function test_el_token_sirve_una_sola_vez(): void
    {
        Notification::fake();

        $gerente = User::factory()->gerente()->create(['email' => 'gerente@nextlevel.edu.pe']);
        $this->post('/forgot-password', ['email' => 'gerente@nextlevel.edu.pe']);

        $token = null;
        Notification::assertSentTo($gerente, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'gerente@nextlevel.edu.pe',
            'password' => 'Clave-Nueva-2026',
            'password_confirmation' => 'Clave-Nueva-2026',
        ])->assertRedirect(route('login'));

        // Reutilizar el mismo enlace ya no funciona.
        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'gerente@nextlevel.edu.pe',
            'password' => 'Otra-Clave-2026',
            'password_confirmation' => 'Otra-Clave-2026',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('Clave-Nueva-2026', $gerente->fresh()->password));
    }

    public function test_un_correo_inexistente_no_revela_que_la_cuenta_no_existe(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nadie@nextlevel.edu.pe'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertSame(
            __('passwords.sent'),
            session('status'),
            'la respuesta debe ser la misma exista o no el correo'
        );

        Notification::assertNothingSent();
    }

    public function test_un_usuario_desactivado_no_recibe_el_enlace(): void
    {
        Notification::fake();

        $inactivo = User::factory()->create([
            'email' => 'inactivo@nextlevel.edu.pe',
            'estado' => 'inactivo',
        ]);

        $this->post('/forgot-password', ['email' => 'inactivo@nextlevel.edu.pe'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_el_cambio_queda_auditado(): void
    {
        Notification::fake();

        $gerente = User::factory()->gerente()->create(['email' => 'gerente@nextlevel.edu.pe']);
        $this->post('/forgot-password', ['email' => 'gerente@nextlevel.edu.pe']);

        $this->assertSame(
            1,
            RegistroMovimiento::where('accion', 'SOLICITAR_RESET')
                ->where('entidad_id', $gerente->id)
                ->count()
        );

        $token = null;
        Notification::assertSentTo($gerente, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'gerente@nextlevel.edu.pe',
            'password' => 'Clave-Nueva-2026',
            'password_confirmation' => 'Clave-Nueva-2026',
        ]);

        $this->assertSame(
            1,
            RegistroMovimiento::where('accion', 'RESTABLECER_CLAVE')
                ->where('entidad_id', $gerente->id)
                ->count()
        );
    }

    /**
     * Sin archivos lang/es, Laravel muestra la clave cruda ("passwords.sent",
     * "validation.required"). Estos tests lo detectan.
     */
    public function test_los_mensajes_se_muestran_en_espanol_y_no_como_claves(): void
    {
        Notification::fake();

        $this->assertSame(
            'Si el correo está registrado, te enviamos el enlace para restablecer tu contraseña.',
            __('passwords.sent')
        );
        $this->assertSame('Tu contraseña ha sido restablecida.', __('passwords.reset'));
        $this->assertStringContainsString('no es válido o ya fue usado', __('passwords.token'));

        $this->assertSame(
            'El campo correo electrónico es obligatorio.',
            __('validation.required', ['attribute' => __('validation.attributes.email')])
        );
        $this->assertSame(
            'El campo correo electrónico debe ser un correo electrónico válido.',
            __('validation.email', ['attribute' => __('validation.attributes.email')])
        );

        // El correo que recibe el personal, en español.
        $this->assertSame('Restablecer mi contraseña', __('Reset Password'));
        $this->assertStringContainsString(
            'solicitaste restablecer la contraseña',
            __('You are receiving this email because we received a password reset request for your account.')
        );

        // Y en pantalla, no aparece ninguna clave sin traducir.
        $this->get('/forgot-password')->assertOk()->assertDontSee('passwords.', false);
        $this->post('/forgot-password', ['email' => 'no-es-un-correo'])
            ->assertSessionHasErrors('email');
    }

    public function test_las_solicitudes_estan_limitadas_para_no_bombardear_el_correo(): void    {
        Notification::fake();

        $gerente = User::factory()->gerente()->create(['email' => 'gerente@nextlevel.edu.pe']);

        $codigos = [];

        for ($i = 0; $i < 8; $i++) {
            $codigos[] = $this->post('/forgot-password', ['email' => 'gerente@nextlevel.edu.pe'])->getStatusCode();
        }

        $this->assertSame(
            2,
            count(array_filter($codigos, fn ($c) => $c === 429)),
            'con throttle 6/min, los intentos 7 y 8 deben responder 429'
        );
        $this->assertSame(
            [302, 302, 302, 302, 302, 302, 429, 429],
            $codigos,
            'con throttle 6/min, los 6 primeros responden y del 7 en adelante 429'
        );
    }
}
