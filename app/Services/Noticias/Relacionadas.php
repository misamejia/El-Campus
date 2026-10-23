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
    /**
     * Máximo de candidatas que se puntúan por similitud.
     *
     * Acota el coste del cálculo: cada candidata exige su vector de embedding
     * y una comparación por coseno en PHP. Con más noticias publicadas, las
     * que quedan fuera son las más antiguas.
     */
    private const CANDIDATAS = 100;

    /**
     * Columnas que necesitan la tarjeta y el cálculo de similitud.
     *
     * @var array<int, string>
     */
    private const COLUMNAS = [
        'id',
        'titulo',
        'slug',
        'resumen',
        'fecha_publicacion',
        'imagen_id',
        'categoria_id',
        'autor_id',
        'embedding',
    ];

    /**
     * Relaciones que usa la tarjeta, con las columnas mínimas.
     *
     * @var array<int, string>
     */
    private const RELACIONES = [
        'categoria:id,nombre',
        'autor:id,nombre',
        'medio:id,ruta,texto_alternativo',
    ];

    public function para(Noticia $noticia, int $limite = 3): Collection
    {
        $vector = is_array($noticia->embedding) && $noticia->embedding !== [] ? $noticia->embedding : null;

        if ($vector !== null) {
            $parecidas = Noticia::query()
                ->publicadas()
                ->with(self::RELACIONES)
                ->where('id', '!=', $noticia->id)
                ->whereNotNull('embedding')
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('id')
                ->limit(self::CANDIDATAS)
                ->get(self::COLUMNAS)
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

        return Noticia::query()
            ->publicadas()
            ->with(self::RELACIONES)
            ->where('id', '!=', $noticia->id)
            ->when($noticia->categoria_id, fn ($q, $categoria) => $q->where('categoria_id', $categoria))
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->take($limite)
            ->get(self::COLUMNAS);
    }
}
