<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_cajero_puede_actualizar_su_propio_perfil(): void
    {
        $user = User::factory()->cajero()->create(['nombre' => 'Nombre anterior']);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'nombre' => 'Nombre actualizado',
                'email' => $user->email,
            ])
            ->assertRedirect();

        $this->assertSame('Nombre actualizado', $user->fresh()->nombre);
    }

    public function test_no_se_permite_usar_un_correo_ya_registrado(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'ocupado@example.com']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'nombre' => $user->nombre,
                'email' => 'ocupado@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_cambiar_el_correo_invalida_la_verificacion_anterior(): void
    {
        $user = User::factory()->create([
            'email' => 'anterior@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'nombre' => $user->nombre,
                'email' => 'nuevo@example.com',
            ])
            ->assertRedirect();

        $this->assertSame('nuevo@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
