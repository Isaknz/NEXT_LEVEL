<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el uso del sistema mientras el usuario conserve la clave temporal.
 *
 * El login ya redirigía al formulario de cambio, pero solo era un aviso: el
 * usuario podía ignorar la redirección y seguir operando con la clave que le
 * entregó el administrador. Con las cuentas semilla eso significaba que
 * "nextlevel2026" seguía sirviendo para entrar al día siguiente.
 */
class DebeCambiarClave
{
    /**
     * Rutas que el usuario sí puede usar mientras tiene clave temporal.
     */
    private const PERMITIDAS = [
        'password.change',
        'password.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! is_null($user->password_changed_at)) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::PERMITIDAS, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Debes cambiar tu contraseña temporal antes de continuar.',
            ], 403);
        }

        return redirect()
            ->route('password.change')
            ->with('status', 'Por seguridad, cambia tu contraseña temporal para poder usar el sistema.');
    }
}
