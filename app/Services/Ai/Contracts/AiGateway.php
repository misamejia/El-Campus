<?php

namespace App\Services\Ai\Contracts;

/**
 * Contrato común para cualquier proveedor de IA (Gemini real o el driver
 * local de demostración). Todas las operaciones deben devolver estructuras
 * sencillas para que el resto de la aplicación no dependa del proveedor.
 */
interface AiGateway
{
    /**
     * Genera un resumen y una lista de puntos clave a partir de la noticia.
     *
     * @return array{resumen: string, puntos_clave: array<int, string>}
     */
    public function resumir(string $titulo, string $contenido): array;

    /**
     * Propone titulares alternativos para la noticia.
     *
     * @return array<int, string>
     */
    public function titulares(string $titulo, string $contenido, int $cantidad = 5): array;

    /**
     * Sugiere etiquetas (palabras clave) para la noticia.
     *
     * @return array<int, string>
     */
    public function etiquetas(string $titulo, string $contenido, int $cantidad = 6): array;

    /**
     * Revisa el estilo del texto y devuelve observaciones.
     *
     * @return array{resumen: string, sugerencias: array<int, array{tipo: string, mensaje: string}>}
     */
    public function revisarEstilo(string $titulo, string $contenido): array;

    /**
     * Devuelve el vector (embedding) que representa un texto.
     *
     * @return array<int, float>
     */
    public function embedding(string $texto): array;
}
