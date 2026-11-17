<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Las rutas de verificación de correo existían, pero el modelo User no
 * implementaba MustVerifyEmail: cualquier visita terminaba en un 500 por
 * llamar a métodos inexistentes.
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pantalla_de_verificacion_responde_200(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get('/email/verify')
            ->assertOk();
    }

    public function test_el_reenvio_de_verificacion_envia_la_notificacion(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->post('/verify-email/email')
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_un_enlace_firmado_marca_el_correo_como_verificado(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect('/dashboard?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_un_usuario_sin_verificar_es_redirigido_al_aviso(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get('/alumnos')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_un_usuario_verificado_si_accede_al_dashboard(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_un_enlace_sin_firma_es_rechazado(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)
            ->get(route('verification.verify', [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]))
            ->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
    }
}
