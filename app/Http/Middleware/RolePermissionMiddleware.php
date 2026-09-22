<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolePermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $role = $user->role;
        $routeName = $request->route()->getName();

        if ($role === 'admin') {
            return $next($request);
        }

        if ($role === 'gerente') {
            if ($routeName === 'users.destroy') {
                abort(403, 'No tienes permiso para eliminar usuarios');
            }
            return $next($request);
        }

        if ($role === 'secretaria') {
            $allowedModules = [
                'alumnos', 'apoderados', 'matriculas', 'pagos',
                'conceptos', 'categorias', 'cajas', 'periodos',
                'niveles', 'grados', 'facultades', 'ciclos',
            ];

            if (str_contains($routeName, 'pagos.anular') || str_contains($routeName, 'gastos.anular')) {
                abort(403, 'No puedes anular pagos o gastos');
            }

            foreach ($allowedModules as $module) {
                if (str_starts_with($routeName, $module . '.')) {
                    return $next($request);
                }
            }

            abort(403);
        }

        if ($role === 'cajero') {
            if ($routeName === 'pagos.create' || $routeName === 'pagos.store') {
                return $next($request);
            }
            if (str_starts_with($routeName, 'cajas.index') || str_starts_with($routeName, 'cajas.show')) {
                return $next($request);
            }
            if ($routeName === 'gastos.create' || $routeName === 'gastos.store') {
                return $next($request);
            }
            abort(403, 'Sin permisos');
        }

        abort(403);
    }
}
