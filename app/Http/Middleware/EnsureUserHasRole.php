<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Verifica que el usuario autenticado tenga uno de los roles permitidos.
     *
     * Un visitante no autenticado se envía al login (igual que el resto de
     * rutas protegidas). Un usuario autenticado sin el rol requerido recibe
     * un 403 en lugar de un redireccionamiento silencioso, diferenciando así
     * "no has iniciado sesión" de "no tienes permisos".
     *
     * Sintaxis de uso en rutas:
     *
     *     ->middleware('role:administrador')
     *     ->middleware('role:administrador,editor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->guest(route('login'));
        }

        if (! in_array($usuario->rol?->codigo, $roles, true)) {
            abort(403, 'Acceso no autorizado');
        }

        return $next($request);
    }
}
