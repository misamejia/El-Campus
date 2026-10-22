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
            ->orderBy('id')
            ->get();

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
     * REGLA 1: un administrador no puede quitarse su propio rol.
     * REGLA 2: la operación no puede dejar al sistema sin administradores.
     * REGLA 3: el nuevo rol debe ser exactamente "administrador" o "editor".
     * REGLA 4: no se aceptan roles arbitrarios (Rule::in).
     * REGLA 5: la ruta está protegida por middleware role:administrador.
     */
    public function actualizarRol(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_EDITOR])],
        ], [
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
        ]);

        $rolId = (int) Rol::where('codigo', $datos['role'])->value('id');
        $administradorId = (int) Rol::where('codigo', User::ROLE_ADMIN)->value('id');

        $quiereDegradar = $usuario->isAdministrador() && $datos['role'] === User::ROLE_EDITOR;

        if ($quiereDegradar && User::where('role_id', $administradorId)->count() === 1) {
            return back()->withErrors(['role' => 'No puedes dejar el sistema sin administradores.']);
        }

        if ($usuario->is($request->user()) && $datos['role'] === User::ROLE_EDITOR) {
            return back()->withErrors(['role' => 'No puedes quitarte tu propio rol de administrador.']);
        }

        $usuario->role_id = $rolId;
        $usuario->save();

        return back()->with('exito', 'El rol del usuario se actualizó correctamente.');
    }
}
