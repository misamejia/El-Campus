<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * Redirige después de verificar el correo.
     *
     * El personal vuelve al panel y un lector a la portada, para que la
     * verificación no termine en un 403 por el middleware de rol.
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        $usuario = $request->user();

        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        if ($usuario?->isAdministrador() || $usuario?->isEditor()) {
            return redirect()->intended(route('dashboard').'?verified=1');
        }

        return redirect()->route('inicio', ['verified' => 1]);
    }
}
