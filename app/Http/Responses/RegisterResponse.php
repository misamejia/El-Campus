<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Redirige al alta pública a la portada.
     *
     * El registro siempre crea un lector: la portada es pública y no exige
     * verificación de correo, así que el usuario no aterriza en una ruta del
     * panel que le respondería 403.
     */
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('inicio');
    }
}
