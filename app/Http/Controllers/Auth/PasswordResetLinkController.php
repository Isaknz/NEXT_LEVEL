<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\RegistraMovimientos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Solicitud del enlace de restablecimiento.
 *
 * Dos criterios importantes:
 *
 * 1. La respuesta es la MISMA exista o no el correo. Si el formulario
 *    respondía "este correo no está registrado", cualquiera podía usar la
 *    pantalla de login para listar los correos del personal.
 * 2. Solo se envía a cuentas activas. Un usuario desactivado que se olvidó su
 *    clave no debe recibir correo: de todos modos no podría entrar.
 */
class PasswordResetLinkController extends Controller
{
    use RegistraMovimientos;

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower(trim($request->input('email')));

        $usuario = User::where('email', $email)->where('estado', 'activo')->first();

        if ($usuario) {
            Password::sendResetLink(['email' => $email]);

            self::registrarMovimiento(
                'SOLICITAR_RESET',
                'Autenticación',
                'User',
                $usuario->id,
                "El usuario {$usuario->nombre} solicitó restablecer su contraseña"
            );
        }

        // Respuesta idéntica exista o no el correo: no revela qué cuentas hay.
        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
