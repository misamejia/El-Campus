<?php

namespace App\Services\Medios;

use App\Models\Medio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Gestión de las imágenes subidas desde el panel.
 *
 * Centraliza las tres operaciones que comparten el panel y el resto del
 * proyecto: guardar el archivo y su registro, resolver el medio a partir de
 * la entrada del formulario y limpiar los medios que ya no usa nadie.
 */
class GestorMedios
{
    /**
     * Guarda la imagen subida en el disco público y registra el medio.
     *
     * El prefijo `storage/` permite usar `asset()` en las vistas públicas sin
     * tocar nada: la carpeta `public/storage` es un enlace al disco `public`.
     */
    public function guardar(Request $request, string $carpeta): Medio
    {
        $archivo = $request->file('imagen');
        $ruta = $archivo->store($carpeta, 'public');

        try {
            return Medio::create([
                'subido_por' => $request->user()?->id,
                'disco' => 'public',
                'ruta' => 'storage/'.$ruta,
                'tipo_mime' => $archivo->getMimeType() ?: Medio::mimeDe($ruta),
                'tamano_bytes' => max(1, (int) $archivo->getSize()),
                'texto_alternativo' => $request->input('imagen_alt'),
            ]);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($ruta);

            throw $e;
        }
    }

    /**
     * Devuelve el id del medio a partir de la entrada del formulario:
     * un archivo subido o una ruta de texto. Null si no hay imagen.
     */
    public function desdeEntrada(Request $request, ?string $ruta, string $carpeta): ?int
    {
        if ($request->hasFile('imagen')) {
            return $this->guardar($request, $carpeta)->id;
        }

        $medio = Medio::desdeRuta($ruta, $request->user()?->id);

        if ($medio !== null) {
            $this->actualizarTextoAlternativo($medio, $request);
        }

        return $medio?->id;
    }

    /**
     * Actualiza el texto alternativo de un medio cuando el formulario lo envía.
     */
    public function actualizarTextoAlternativo(Medio $medio, Request $request): void
    {
        if ($request->exists('imagen_alt')) {
            $medio->update(['texto_alternativo' => $request->input('imagen_alt')]);
        }
    }

    /**
     * Elimina el archivo y el registro de un medio subido por el panel.
     *
     * Las imágenes demo que viven en `public/imagenes` (sin prefijo `storage/`)
     * se conservan, ya que no pertenecen al disco administrable y pueden ser
     * compartidas. Solo se borra el medio si ningún otro registro lo usa.
     */
    public function limpiar(?Medio $medio): void
    {
        if ($medio === null || ! str_starts_with($medio->ruta, 'storage/')) {
            return;
        }

        if ($medio->noticias()->exists() || $medio->eventos()->exists()) {
            return;
        }

        $ruta = str_replace('\\', '/', Str::after($medio->ruta, 'storage/'));

        if (str_contains($ruta, '..') || ! str_starts_with($ruta, 'imagenes/')) {
            return;
        }

        $medio->delete();

        Storage::disk('public')->delete($ruta);
    }
}
