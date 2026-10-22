<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use App\Services\Ai\AiManager;
use Illuminate\Console\Command;
use Throwable;

/**
 * Genera en lote los datos de IA de las noticias existentes.
 *
 * Ejemplos:
 *   php artisan ai:backfill
 *   php artisan ai:backfill --solo=resumen --force
 */
class AiBackfill extends Command
{
    protected $signature = 'ai:backfill
        {--force : Regenera aunque el campo ya tenga valor}
        {--solo=todo : Qué generar: embedding, resumen, etiquetas o todo}';

    protected $description = 'Genera con IA los embeddings, resúmenes y etiquetas de las noticias.';

    public function handle(AiManager $ai): int
    {
        $solo = (string) $this->option('solo');
        $force = (bool) $this->option('force');

        $this->info("Driver de IA: {$ai->nombre()}");
        $this->line("Generando: {$solo}".($force ? ' (forzando regeneración)' : ''));

        $total = 0;
        $fallos = 0;

        Noticia::query()->chunkById(50, function ($noticias) use ($ai, $solo, $force, &$total, &$fallos) {
            foreach ($noticias as $noticia) {
                $total++;

                try {
                    $this->generarEmbedding($ai, $noticia, $solo, $force);
                    $this->generarResumen($ai, $noticia, $solo, $force);
                    $this->generarEtiquetas($ai, $noticia, $solo, $force);
                    $noticia->saveQuietly();

                    $this->line("  ✓ #{$noticia->id} {$noticia->titulo}");
                } catch (Throwable $e) {
                    $fallos++;
                    $this->error("  ✗ #{$noticia->id} {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info("Procesadas {$total} noticias. Fallos: {$fallos}.");

        return self::SUCCESS;
    }

    private function generarEmbedding(AiManager $ai, Noticia $noticia, string $solo, bool $force): void
    {
        if (! $this->genera($solo, 'embedding') || (! $force && ! empty($noticia->embedding))) {
            return;
        }

        $noticia->embedding = $ai->embedding($this->texto($noticia));
        $noticia->embedding_actualizado_en = now();
    }

    private function generarResumen(AiManager $ai, Noticia $noticia, string $solo, bool $force): void
    {
        if (! $this->genera($solo, 'resumen')) {
            return;
        }

        if (! $force && filled($noticia->resumen) && ! empty($noticia->puntos_clave)) {
            return;
        }

        $resultado = $ai->resumir($noticia->titulo, $noticia->contenido);

        if ($force || blank($noticia->resumen)) {
            $noticia->resumen = $resultado['resumen'];
        }

        if ($force || empty($noticia->puntos_clave)) {
            $noticia->puntos_clave = $resultado['puntos_clave'];
        }
    }

    private function generarEtiquetas(AiManager $ai, Noticia $noticia, string $solo, bool $force): void
    {
        if (! $this->genera($solo, 'etiquetas') || (! $force && ! empty($noticia->etiquetas_ia))) {
            return;
        }

        $noticia->etiquetas_ia = $ai->etiquetas($noticia->titulo, $noticia->contenido);
    }

    private function genera(string $solo, string $campo): bool
    {
        return $solo === 'todo' || $solo === $campo;
    }

    private function texto(Noticia $noticia): string
    {
        return trim($noticia->titulo."\n".($noticia->resumen ?? '')."\n".$noticia->contenido);
    }
}
