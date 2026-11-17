<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // No usamos proxy inverso, así que TrustProxies no aporta nada.
        // Si algún día despliegas detrás de Nginx/Cloudflare, elimina esta
        // línea y configura los proxies confiables con $middleware->trustProxies(...).
        $middleware->remove(\Illuminate\Http\Middleware\TrustProxies::class);

        $middleware->alias([
            'check.role' => \App\Http\Middleware\CheckRole::class,
            'role.permission' => \App\Http\Middleware\RolePermissionMiddleware::class,
            'clave.temporal' => \App\Http\Middleware\DebeCambiarClave::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
