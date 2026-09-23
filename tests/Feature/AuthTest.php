<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_login_success(): void
    {
        $this->withoutMiddleware('throttle:login');
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->withoutMiddleware('throttle:login');
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_password_change_flow(): void
    {
        $this->withoutMiddleware('throttle:login');
        $user = User::factory()->create([
            'password' => Hash::make('temporal123'),
            'password_changed_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'temporal123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('password.change', absolute: false));

        $response = $this->put('/password/change', [
            'password_current' => 'temporal123',
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ]);

        $this->assertAuthenticated();
        $this->assertTrue(Hash::check('nueva123', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->password_changed_at);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $this->withoutMiddleware('throttle:login');
        $user = User::factory()->create([
            'estado' => 'inactivo',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}
