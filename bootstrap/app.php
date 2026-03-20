<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Middleware de Sanctum para API
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Alias de middleware
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | AQUÍ LA MAGIA ✨
        |--------------------------------------------------------------------------
        | Le decimos a Laravel que NO valide el CSRF en estas rutas
        | para que Postman pueda hacer peticiones sin enviar el token.
        |
        */

        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'api/login',
            'api/register',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();