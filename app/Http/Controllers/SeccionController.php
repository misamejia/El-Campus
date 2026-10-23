<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\View\View;

class SeccionController extends Controller
{
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
        $seccion = $seccion ?? request()->route('seccion');
        $categoria = Categoria::where('slug', $seccion)
            ->where('activa', true)
            ->firstOrFail();

        $noticias = $categoria->noticias()
            ->with(['categoria', 'autor', 'estado', 'medio'])
            ->delEstado('publicada')
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->get();

        return view('public.categoria', [
            'seccion' => $categoria->slug,
            'categoria' => $categoria,
            'noticias' => $noticias,
        ]);
    }
}
