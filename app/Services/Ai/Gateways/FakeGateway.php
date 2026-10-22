<?php

namespace App\Services\Ai\Gateways;

use App\Services\Ai\Contracts\AiGateway;
use Illuminate\Support\Str;

/**
 * Driver local de demostración: no hace ninguna llamada de red y responde con
 * heurísticas sencillas. Permite probar toda la interfaz (y los tests) sin
 * necesidad de una API key.
 */
class FakeGateway implements AiGateway
{
    public function resumir(string $titulo, string $contenido): array
    {
        $frases = $this->frases($contenido);
        $resumen = Str::limit(implode(' ', array_slice($frases, 0, 2)), 280, '…');

        $puntos = array_slice($frases, 0, 4);
        if ($puntos === [] && $titulo !== '') {
            $puntos = [$titulo];
        }

        return [
            'resumen' => $resumen !== '' ? $resumen : $titulo,
            'puntos_clave' => array_map(fn (string $p) => Str::limit($p, 140), $puntos),
        ];
    }

    public function titulares(string $titulo, string $contenido, int $cantidad = 5): array
    {
        $base = trim($titulo) !== ''
            ? trim($titulo)
            : Str::limit($this->frases($contenido)[0] ?? 'La noticia', 80);

        $base = rtrim($base, '.');

        $propuestas = [
            $base,
            "{$base}: lo que debes saber",
            "Claves para entender: {$base}",
            "Así avanza {$base}",
            "{$base}, en detalle",
            "Todo lo que se sabe sobre {$base}",
        ];

        return array_slice(array_values(array_unique($propuestas)), 0, $cantidad);
    }

    public function etiquetas(string $titulo, string $contenido, int $cantidad = 6): array
    {
        $texto = mb_strtolower($titulo.' '.$contenido);
        $texto = (string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texto);
        $palabras = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $frecuencias = [];
        foreach ($palabras as $palabra) {
            if (mb_strlen($palabra) < 5 || in_array($palabra, $this->vacias(), true)) {
                continue;
            }
            $frecuencias[$palabra] = ($frecuencias[$palabra] ?? 0) + 1;
        }

        arsort($frecuencias);

        return array_slice(array_keys($frecuencias), 0, $cantidad);
    }

    public function revisarEstilo(string $titulo, string $contenido): array
    {
        $sugerencias = [];

        if (mb_strlen($titulo) > 90) {
            $sugerencias[] = ['tipo' => 'claridad', 'mensaje' => 'El titular supera los 90 caracteres; conviene acortarlo.'];
        }
        if (str_contains($contenido, '  ')) {
            $sugerencias[] = ['tipo' => 'ortografía', 'mensaje' => 'Se detectaron espacios dobles en el texto.'];
        }

        $largas = collect($this->frases($contenido))->filter(fn (string $o) => str_word_count($o) > 35)->count();
        if ($largas > 0) {
            $sugerencias[] = ['tipo' => 'estilo', 'mensaje' => "Hay {$largas} frase(s) de más de 35 palabras; divídelas para facilitar la lectura."];
        }

        if ($sugerencias === []) {
            $sugerencias[] = ['tipo' => 'estilo', 'mensaje' => 'No se detectaron problemas evidentes de estilo.'];
        }

        return [
            'resumen' => 'Revisión heurística local (modo demo).',
            'sugerencias' => $sugerencias,
        ];
    }

    public function embedding(string $texto): array
    {
        $dimensiones = max(2, (int) config('ai.embedding_dimensions', 256));
        $vector = array_fill(0, $dimensiones, 0.0);

        $tokens = preg_split('/\W+/u', mb_strtolower($texto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) < 3) {
                continue;
            }
            $vector[crc32($token) % $dimensiones] += 1.0;
        }

        $norma = sqrt(array_sum(array_map(fn (float $v) => $v * $v, $vector)));
        if ($norma > 0) {
            $vector = array_map(fn (float $v) => $v / $norma, $vector);
        }

        return $vector;
    }

    /**
     * @return array<int, string>
     */
    private function frases(string $texto): array
    {
        $texto = trim($texto);
        if ($texto === '') {
            return [];
        }

        $partes = preg_split('/(?<=[.!?])\s+|\n+/u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter(array_map('trim', $partes)));
    }

    /**
     * @return array<int, string>
     */
    private function vacias(): array
    {
        return ['sobre', 'entre', 'desde', 'hasta', 'también', 'porque', 'cuando', 'donde', 'mientras',
            'durante', 'aunque', 'según', 'mediante', 'universidad', 'noticia', 'campus', 'para',
            'este', 'esta', 'estos', 'estas', 'como', 'pero', 'más', 'muy', 'cada', 'todo', 'toda',
            'hacia', 'contra', 'hacia', 'otras', 'otros', 'estos'];
    }
}
