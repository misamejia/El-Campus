<?php

namespace App\Services\Ai\Gateways;

use App\Services\Ai\Contracts\AiGateway;
use Illuminate\Support\Str;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

/**
 * Proveedor real basado en Google Gemini a través de Prism.
 */
class GeminiGateway implements AiGateway
{
    public function resumir(string $titulo, string $contenido): array
    {
        $schema = new ObjectSchema(
            name: 'resumen_noticia',
            description: 'Resumen y puntos clave de una noticia periodística',
            properties: [
                new StringSchema('resumen', 'Resumen objetivo con estilo de entradilla, máximo 60 palabras'),
                new ArraySchema('puntos_clave', 'Entre 3 y 5 hechos clave', new StringSchema('punto', 'Un hecho clave como frase breve')),
            ],
            requiredFields: ['resumen', 'puntos_clave'],
        );

        $datos = $this->estructurado($schema, "Resume la noticia y extrae sus puntos clave.\n\n".$this->cuerpo($titulo, $contenido));

        return [
            'resumen' => trim((string) ($datos['resumen'] ?? '')),
            'puntos_clave' => $this->limpiarLista($datos['puntos_clave'] ?? [], 6),
        ];
    }

    public function titulares(string $titulo, string $contenido, int $cantidad = 5): array
    {
        $schema = new ObjectSchema(
            name: 'titulares_noticia',
            description: 'Titulares alternativos para una noticia',
            properties: [
                new ArraySchema('titulares', 'Titulares atractivos pero veraces, sin sensacionalismo', new StringSchema('titular', 'Un titular')),
            ],
            requiredFields: ['titulares'],
        );

        $instruccion = "Propón {$cantidad} titulares alternativos para esta noticia.\n\n".$this->cuerpo($titulo, $contenido);
        $datos = $this->estructurado($schema, $instruccion);

        return $this->limpiarLista($datos['titulares'] ?? [], $cantidad);
    }

    public function etiquetas(string $titulo, string $contenido, int $cantidad = 6): array
    {
        $schema = new ObjectSchema(
            name: 'etiquetas_noticia',
            description: 'Palabras clave que describen la noticia',
            properties: [
                new ArraySchema('etiquetas', 'Palabras clave en minúsculas, sin repetir', new StringSchema('etiqueta', 'Una palabra o término breve')),
            ],
            requiredFields: ['etiquetas'],
        );

        $instruccion = "Extrae hasta {$cantidad} palabras clave que describan esta noticia.\n\n".$this->cuerpo($titulo, $contenido);
        $datos = $this->estructurado($schema, $instruccion);

        return $this->limpiarLista($datos['etiquetas'] ?? [], $cantidad, true);
    }

    public function revisarEstilo(string $titulo, string $contenido): array
    {
        $sugerencia = new ObjectSchema(
            'sugerencia',
            'Una observación de estilo',
            [
                new StringSchema('tipo', 'Categoría breve: ortografía, claridad, estilo o datos'),
                new StringSchema('mensaje', 'Descripción de la observación y cómo corregirla'),
            ],
            ['tipo', 'mensaje'],
        );

        $schema = new ObjectSchema(
            name: 'revision_estilo',
            description: 'Revisión de estilo de un texto periodístico',
            properties: [
                new StringSchema('resumen', 'Valoración global breve del texto'),
                new ArraySchema('sugerencias', 'Lista de observaciones concretas. Vacía si el texto está correcto', $sugerencia),
            ],
            requiredFields: ['resumen', 'sugerencias'],
        );

        $datos = $this->estructurado($schema, "Revisa el estilo, la claridad y la ortografía.\n\n".$this->cuerpo($titulo, $contenido));

        $sugerencias = collect($datos['sugerencias'] ?? [])
            ->map(fn ($s) => [
                'tipo' => trim((string) ($s['tipo'] ?? 'estilo')),
                'mensaje' => trim((string) ($s['mensaje'] ?? '')),
            ])
            ->filter(fn ($s) => $s['mensaje'] !== '')
            ->values()
            ->all();

        return [
            'resumen' => trim((string) ($datos['resumen'] ?? '')),
            'sugerencias' => $sugerencias,
        ];
    }

    public function embedding(string $texto): array
    {
        $respuesta = Prism::embeddings()
            ->using(Provider::Gemini, $this->modeloEmbedding())
            ->fromInput(Str::limit($texto, (int) config('ai.max_caracteres', 8000)))
            ->withClientRetry(2, 200)
            ->asEmbeddings();

        return array_map('floatval', $respuesta->embeddings[0]->embedding);
    }

    /**
     * Ejecuta una petición estructurada y devuelve el arreglo resultante.
     *
     * @return array<string, mixed>
     */
    private function estructurado(Schema $schema, string $instruccion): array
    {
        $respuesta = Prism::structured()
            ->using(Provider::Gemini, $this->modelo())
            ->withSchema($schema)
            ->withSystemPrompt($this->sistema())
            ->withPrompt($instruccion)
            ->withClientRetry(2, 200)
            ->asStructured();

        return is_array($respuesta->structured) ? $respuesta->structured : [];
    }

    private function cuerpo(string $titulo, string $contenido): string
    {
        $limite = (int) config('ai.max_caracteres', 8000);

        return "Titular: {$titulo}\n\nCuerpo:\n".Str::limit($contenido, $limite);
    }

    /**
     * @param  array<int, mixed>  $valores
     * @return array<int, string>
     */
    private function limpiarLista(array $valores, int $cantidad, bool $minusculas = false): array
    {
        return collect($valores)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->when($minusculas, fn ($c) => $c->map(fn ($v) => mb_strtolower($v)))
            ->unique()
            ->take($cantidad)
            ->values()
            ->all();
    }

    private function modelo(): string
    {
        return (string) config('ai.text_model');
    }

    private function modeloEmbedding(): string
    {
        return (string) config('ai.embedding_model');
    }

    private function sistema(): string
    {
        return 'Eres un editor periodístico de un periódico universitario en español (Colombia). '
            .'Escribes con tono objetivo, claro y correcto. Respondes siempre en español.';
    }
}
