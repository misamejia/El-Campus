<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAutorRequest;
use App\Http\Requests\UpdateAutorRequest;
use App\Models\Autor;
use App\Support\SlugUnico;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AutorController extends Controller
{
    /**
     * Lista los autores con la cantidad de noticias asociadas.
     */
    public function index(): View
    {
        $autores = Autor::withCount('noticias')
            ->orderBy('nombre')
            ->get();

        return view('autores.index', compact('autores'));
    }

    /**
     * Muestra el formulario para crear un autor.
     */
    public function create(): View
    {
        return view('autores.create');
    }

    /**
     * Almacena un autor nuevo.
     */
    public function store(StoreAutorRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['slug'] = SlugUnico::generar($request->input('slug') ?: $request->input('nombre'), Autor::class);
        $datos['activo'] = $request->boolean('activo');

        Autor::create($datos);

        return redirect()->route('autores.index')
            ->with('exito', 'Autor creado correctamente.');
    }

    /**
     * Muestra un autor y las noticias asociadas.
     */
    public function show(Autor $autor): View
    {
        $autor->load('noticias.categoria');

        return view('autores.show', compact('autor'));
    }

    /**
     * Muestra el formulario para editar un autor.
     */
    public function edit(Autor $autor): View
    {
        return view('autores.edit', compact('autor'));
    }

    /**
     * Actualiza un autor existente conservando la unicidad del slug.
     */
    public function update(UpdateAutorRequest $request, Autor $autor): RedirectResponse
    {
        $datos = $request->validated();
        $datos['slug'] = SlugUnico::generar($request->input('slug') ?: $request->input('nombre'), Autor::class, $autor->id);
        $datos['activo'] = $request->boolean('activo');

        $autor->update($datos);

        return redirect()->route('autores.index')
            ->with('exito', 'Autor actualizado correctamente.');
    }

    /**
     * Elimina un autor siempre que no tenga noticias asociadas.
     *
     * La clave foránea con restrictOnDelete protege la base de datos; aquí
     * además se ofrece un mensaje claro para el usuario.
     */
    public function destroy(Autor $autor): RedirectResponse
    {
        if ($autor->noticias()->exists()) {
            return redirect()->route('autores.index')
                ->with('error', 'No se puede eliminar este autor porque tiene noticias asociadas.');
        }

        $autor->delete();

        return redirect()->route('autores.index')
            ->with('exito', 'Autor eliminado correctamente.');
    }
}
