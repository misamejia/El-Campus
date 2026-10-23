<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirige según el rol tras un inicio de sesión correcto.
     *
     * El personal con acceso al panel va a /dashboard; un lector va a la
     * portada. El lector nunca se envía a una ruta del panel (aunque hubiera
     * una URL "intended" guardada), porque allí recibiría un 403.
     */
    public function toResponse($request): RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario?->isAdministrador() || $usuario?->isEditor()) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('inicio');
    }
}
