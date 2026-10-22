<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Generación de slugs únicos para las entidades del periódico.
 *
 * Centraliza el criterio que comparten noticias, eventos, categorías y
 * autores: se normaliza el texto con Str::slug y, si el resultado ya existe,
 * se añaden sufijos numéricos (-2, -3...) hasta obtener uno libre.
 *
 * El slug también está protegido por el índice único de cada tabla: esta
 * ayuda solo evita el error de base de datos y genera una URL legible.
 */
final class SlugUnico
{
    /**
     * Genera un slug único a partir de un texto base.
     *
     * @param  class-string<Model>  $modelo  Modelo donde se comprueba la unicidad.
     * @param  int|null  $ignorarId  Registro que se puede reutilizar (al editar).
     */
    public static function generar(string $base, string $modelo, ?int $ignorarId = null): string
    {
        $base = Str::slug($base);
        $slug = $base;
        $sufijo = 2;

        while ($modelo::where('slug', $slug)->when($ignorarId, fn ($consulta) => $consulta->where('id', '!=', $ignorarId))->exists()) {
            $slug = $base.'-'.$sufijo;
            $sufijo++;
        }

        return $slug;
    }
}
