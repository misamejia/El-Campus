<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Cabeceras de seguridad que El Campus envía en toda respuesta.
     *
     * - X-Frame-Options: DENY  ->  evita que el sitio se incruste en un iframe
     *   (protección contra clickjacking).
     * - X-Content-Type-Options: nosniff  ->  el navegador no "adivina" el tipo
     *   MIME de una respuesta (evita interpretar contenido como ejecutable).
     * - Referrer-Policy: strict-origin-when-cross-origin  ->  al salir a otro
     *   sitio solo se envía el origen (y no la URL completa) en HTTPS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return self::aplicar($next($request));
    }

    /**
     * Aplica las cabeceras a cualquier respuesta, incluidas las de error
     * (404, 403, 419), que no pasan por el cierre de handle().
     */
    public static function aplicar(Response $response): Response
    {
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
