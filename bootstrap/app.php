<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // El control de rol debe ejecutarse antes de resolver el modelo de la
        // ruta. Si se resuelve primero, un usuario sin permisos recibe un 404
        // (registro inexistente) en lugar de un 403, lo que además revela qué
        // identificadores existen en cada tabla.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: EnsureUserHasRole::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Las respuestas de error (404, 403, 419) también llevan las cabeceras
        // de seguridad, porque no atraviesan el cierre de handle().
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            return SecurityHeaders::aplicar($response);
        });
    })->create();
