<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->estado === 'inactivo') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
                ]);
            }

            $request->session()->regenerate();

            // Registrar el inicio de sesión
            self::registrarMovimiento(
                'INICIAR_SESION',
                'Autenticación',
                'User',
                $user->id,
                "El usuario {$user->nombre} inició sesión"
            );

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            // Registrar el cierre de sesión
            self::registrarMovimiento(
                'INICIAR_SESION',
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
