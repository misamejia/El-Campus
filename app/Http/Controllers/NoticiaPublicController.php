<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Services\Noticias\Relacionadas;
use Illuminate\View\View;

class NoticiaPublicController extends Controller
{
    /**
     * Noticia pública.
     *
     * Busca por slug y únicamente muestra noticias publicadas.
     * Los borradores, las archivadas y los slugs inexistentes
     * producen 404 (firstOrFail).
     */
    public function publica(string $slug): View
    {
        $noticia = Noticia::with(['categoria', 'autor', 'estado', 'medio'])
            ->where('slug', $slug)
            ->delEstado('publicada')
            ->firstOrFail();

        return view('public.noticia', [
            'seccion' => $noticia->categoria?->slug,
            'noticia' => $noticia,
            'relacionadas' => app(Relacionadas::class)->para($noticia, (int) config('ai.relacionadas', 3)),
        ]);
    }
}
