<?php

namespace App\Services\Noticias;

use App\Models\Noticia;
use App\Support\Similitud;
use Illuminate\Support\Collection;

/**
 * Calcula "noticias relacionadas" por similitud semántica usando los
 * embeddings guardados. Si la noticia aún no tiene embedding, cae a las
 * noticias más recientes de la misma categoría.
 */
class Relacionadas
{
    public function para(Noticia $noticia, int $limite = 3): Collection
    {
        $vector = is_array($noticia->embedding) && $noticia->embedding !== [] ? $noticia->embedding : null;

        if ($vector !== null) {
            $parecidas = Noticia::publicadas()
                ->where('id', '!=', $noticia->id)
                ->get()
                ->filter(fn (Noticia $n) => is_array($n->embedding) && $n->embedding !== [])
                ->map(fn (Noticia $n) => ['noticia' => $n, 'score' => Similitud::coseno($vector, $n->embedding)])
                ->sortByDesc('score')
                ->take($limite)
                ->pluck('noticia')
                ->values();

            if ($parecidas->isNotEmpty()) {
                return $parecidas;
            }
        }

        return Noticia::publicadas()
            ->where('id', '!=', $noticia->id)
            ->when($noticia->categoria_id, fn ($q, $categoria) => $q->where('categoria_id', $categoria))
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->take($limite)
            ->get();
    }
}
