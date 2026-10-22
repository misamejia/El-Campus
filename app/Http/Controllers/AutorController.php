<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);
        $datos['slug'] = $this->slugUnico($request->input('slug'), $request->input('nombre'));
        $datos['activo'] = $request->boolean('activo');

        Autor::create($datos);

        return redirect()->route('autores.index')
            ->with('exito', 'Autor creado correctamente.');
    }

    /**
     * Muestra un autor y las noticias asociadas.
     */
    public function show(string $autor): View
    {
        $autor = Autor::with('noticias.categoria')->findOrFail($autor);

        return view('autores.show', compact('autor'));
    }

    /**
     * Muestra el formulario para editar un autor.
     */
    public function edit(string $autor): View
    {
        $autor = Autor::findOrFail($autor);

        return view('autores.edit', compact('autor'));
    }

    /**
     * Actualiza un autor existente conservando la unicidad del slug.
     */
    public function update(Request $request, string $autor): RedirectResponse
    {
        $autor = Autor::findOrFail($autor);

        $datos = $this->validar($request, $autor->id);
        $datos['slug'] = $this->slugUnico($request->input('slug'), $request->input('nombre'), $autor->id);
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
    public function destroy(string $autor): RedirectResponse
    {
        $autor = Autor::findOrFail($autor);

        if ($autor->noticias()->exists()) {
            return redirect()->route('autores.index')
                ->with('error', 'No se puede eliminar este autor porque tiene noticias asociadas.');
        }

        $autor->delete();

        return redirect()->route('autores.index')
            ->with('exito', 'Autor eliminado correctamente.');
    }

    /**
     * Reglas de validación para crear y actualizar autores.
     *
     * @param  array<string>  $reglasAdicionales
     */
    private function validar(Request $request, ?int $idActual = null): array
    {
        $reglas = [
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('autores', 'slug')->ignore($idActual)],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ];

        return $request->validate($reglas);
    }

    /**
     * Genera un slug único a partir del slug propuesto o del nombre.
     */
    private function slugUnico(?string $slugPropuesto, string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($slugPropuesto ?: $nombre);
        $slug = $base;
        $sufijo = 2;

        while (Autor::where('slug', $slug)->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))->exists()) {
            $slug = $base.'-'.$sufijo;
            $sufijo++;
        }

        return $slug;
    }
}