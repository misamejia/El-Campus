<?php

namespace App\Services\Noticias;

use App\Models\Noticia;
use App\Services\Ai\AiManager;
use Throwable;

/**
 * Generación de los campos de IA de una noticia.
 *
 * El proveedor se resuelve de forma perezosa dentro del bloque try para que
 * un problema de configuración (una API key que caduca, un proveedor mal
 * definido) degrade el guardado en silencio en lugar de devolver un error 500.
 */
class GeneradorIa
{
    /**
     * Genera con IA el embedding, el resumen y las etiquetas de una noticia.
     *
     * Nunca interrumpe el guardado: cualquier fallo del proveedor se registra
     * y la noticia se conserva tal y como la escribió la persona.
     */
    public function generar(Noticia $noticia, string $estadoCodigo, bool $contenidoCambio): void
    {
        try {
            $ai = app(AiManager::class);

            if ($contenidoCambio || ! is_array($noticia->embedding) || $noticia->embedding === []) {
                $texto = trim($noticia->titulo."\n".($noticia->resumen ?? '')."\n".$noticia->contenido);
                $noticia->embedding = $ai->embedding($texto);
                $noticia->embedding_actualizado_en = now();
            }

            if ($estadoCodigo === 'publicada' && blank($noticia->resumen)) {
                $resultado = $ai->resumir($noticia->titulo, $noticia->contenido);
                $noticia->resumen = $resultado['resumen'];
                if (empty($noticia->puntos_clave)) {
                    $noticia->puntos_clave = $resultado['puntos_clave'];
                }
            }

            if (empty($noticia->etiquetas_ia)) {
                $noticia->etiquetas_ia = $ai->etiquetas($noticia->titulo, $noticia->contenido);
            }

            $noticia->saveQuietly();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
