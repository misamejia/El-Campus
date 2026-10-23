<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use App\Support\SlugUnico;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    /**
     * Lista las categorías con la cantidad de noticias asociadas.
     */
    public function index(): View
    {
        $categorias = Categoria::withCount('noticias')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Muestra el formulario para crear una categoría.
     */
    public function create(): View
    {
        return view('categorias.create');
    }

    /**
     * Almacena una categoría nueva.
     */
    public function store(StoreCategoriaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['slug'] = SlugUnico::generar($request->input('slug') ?: $request->input('nombre'), Categoria::class);
        $datos['activa'] = $request->boolean('activa');

        Categoria::create($datos);

        return redirect()->route('categorias.index')
            ->with('exito', 'Categoría creada correctamente.');
    }

    /**
     * Muestra una categoría y las noticias asociadas.
     */
    public function show(Categoria $categoria): View
    {
        $categoria->load(['noticias.autor', 'noticias.estado', 'noticias.medio']);

        return view('categorias.show', compact('categoria'));
    }

    /**
     * Muestra el formulario para editar una categoría.
     */
    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Actualiza una categoría existente conservando la unicidad del slug.
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $datos = $request->validated();
        $datos['slug'] = SlugUnico::generar($request->input('slug') ?: $request->input('nombre'), Categoria::class, $categoria->id);
        $datos['activa'] = $request->boolean('activa');

        $categoria->update($datos);

        return redirect()->route('categorias.index')
            ->with('exito', 'Categoría actualizada correctamente.');
    }

    /**
     * Elimina una categoría siempre que no tenga noticias asociadas.
     *
     * La clave foránea protege la base de datos; aquí además se ofrece un
     * mensaje claro para el usuario.
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->noticias()->exists()) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar esta categoría porque tiene noticias asociadas.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('exito', 'Categoría eliminada correctamente.');
    }

    /**
     * Página editorial de una sección del periódico.
     *
     * Recibe el slug de la categoría (proveniente de una ruta estática).
     * Solo se muestran noticias publicadas, ordenadas por fecha de
     * publicación descendente, con eager loading de categoria, autor, estado
     * y medio. Las categorías inactivas o inexistentes producen 404.
     */
    public function seccion(?string $seccion = null): View
    {
        abort(404);
    }
}
