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

        // Si usas SPA + cookies con Sanctum (stateful)
        $middleware->statefulApi();

        // Si tienes este middleware custom para normalizar inputs:
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\CamelToSnakeInput::class,
        ]);

        // Aliases que ya venías usando (si los necesitas aquí)
        $middleware->alias([
            'auth'   => \App\Http\Middleware\Authenticate::class,
            'can'    => \Illuminate\Auth\Middleware\Authorize::class,

            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability'   => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,

            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
