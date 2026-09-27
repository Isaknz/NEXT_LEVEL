<?php

namespace App\Http\Middleware;

use App\Support\Permisos;
use Closure;
use Illuminate\Http\Request;

class RolePermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $routeName = $request->route()?->getName();

        if (Permisos::permite($request->user()->role, $routeName)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a esta sección');
    }
}
