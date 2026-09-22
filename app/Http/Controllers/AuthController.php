<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Traits\RegistraMovimientos;

class AuthController extends Controller
{
    use RegistraMovimientos;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->estado === 'inactivo') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
                ]);
            }

            $request->session()->regenerate();

            // Actualizar último acceso
            $user->update(['last_login' => now()]);

            // Registrar el inicio de sesión
            self::registrarMovimiento(
                'INICIAR_SESION',
                'Autenticación',
                'User',
                $user->id,
                "El usuario {$user->nombre} inició sesión"
            );

            // Advertencia si es el primer inicio de sesión
            if (is_null($user->password_changed_at)) {
                return redirect()->route('password.change')->with('status', '¡Bienvenido! Por favor, cambia tu contraseña temporal.');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ])->onlyInput('email');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_current' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = auth()->user();
        if (!Hash::check($request->password_current, $user->password)) {
            return back()->withErrors(['password_current' => 'La contraseña actual es incorrecta.']);
        }
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);
        return back()->with('status', 'Contraseña actualizada');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            // Registrar el cierre de sesión
            self::registrarMovimiento(
                'CERRAR_SESION',
                'Autenticación',
                'User',
                $user->id,
                "El usuario {$user->nombre} cerró sesión"
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
