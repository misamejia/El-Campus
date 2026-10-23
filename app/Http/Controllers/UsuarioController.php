<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Lista los usuarios registrados con su rol actual.
     *
     * Esta sección únicamente permite gestionar roles; no ofrece cambio de
     * contraseña, correo, eliminación ni edición de perfiles.
     */
    public function index(): View
    {
        $usuarios = User::with('rol')
            ->orderBy('name')
            ->orderByDesc('id')
            ->paginate(15);

        $totalAdministradores = User::whereHas(
            'rol',
            fn ($rol) => $rol->where('codigo', User::ROLE_ADMIN)
        )->count();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'totalAdministradores' => $totalAdministradores,
        ]);
    }

    /**
     * Cambia el rol de un usuario verificando las reglas de seguridad.
     *
     * REGLA 1: un administrador no puede cambiar su propio rol.
     * REGLA 2: la operación no puede dejar al sistema sin administradores.
     * REGLA 3: el nuevo rol debe ser "administrador", "editor" o "lector".
     * REGLA 4: no se aceptan roles arbitrarios (Rule::in).
     * REGLA 5: no se asigna un rol de edición a una cuenta sin verificar.
     * REGLA 6: la ruta está protegida por middleware role:administrador.
     */
    public function actualizarRol(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
        ], [
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
        ]);

        $rolId = (int) Rol::where('codigo', $datos['role'])->value('id');
        $administradorId = (int) Rol::where('codigo', User::ROLE_ADMIN)->value('id');

        $cambiaRol = (int) $usuario->role_id !== $rolId;
        $dejaDeSerAdministrador = $usuario->isAdministrador() && $datos['role'] !== User::ROLE_ADMIN;

        if ($dejaDeSerAdministrador && User::where('role_id', $administradorId)->count() === 1) {
            return back()->withErrors(['role' => 'No puedes dejar el sistema sin administradores.']);
        }

        if ($usuario->is($request->user()) && $cambiaRol) {
            return back()->withErrors(['role' => 'No puedes cambiar tu propio rol.']);
        }

        $asignaRolDeEdicion = in_array($datos['role'], [User::ROLE_ADMIN, User::ROLE_EDITOR], true);

        if ($cambiaRol && $asignaRolDeEdicion && ! $usuario->hasVerifiedEmail()) {
            return back()->withErrors([
                'role' => 'No puedes asignar un rol de edición a una cuenta sin correo verificado.',
            ]);
        }

        $usuario->role_id = $rolId;
        $usuario->save();

        return back()->with('exito', 'El rol del usuario se actualizó correctamente.');
    }
}
