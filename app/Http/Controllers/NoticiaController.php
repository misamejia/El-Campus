<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\EstadoNoticia;
use App\Models\Medio;
use App\Models\Noticia;
use App\Services\Ai\AiManager;
use App\Services\Noticias\Relacionadas;
use App\Support\SlugUnico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class NoticiaController extends Controller
{
    /**
     * Lista las noticias ordenadas por su fecha de publicación.
     */
    public function index(): View
    {
        $noticias = Noticia::with(['categoria', 'autor', 'estado', 'medio'])
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->get();

        return view('noticias.index', compact('noticias'));
    }

    /**
     * Muestra el formulario para crear una noticia.
     */
    public function create(): View
    {
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $autores = Autor::where('activo', true)->orderBy('nombre')->get();

        return view('noticias.create', compact('categorias', 'autores'));
    }

    /**
     * Almacena una noticia nueva.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = array_merge($this->validar($request, null, $request->hasFile('imagen')), $this->camposIa($request));
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Noticia::class);
        $datos['destacada'] = $request->boolean('destacada');
        $estadoCodigo = $datos['estado'];
        $datos['estado_id'] = $this->estadoId($estadoCodigo);
        $datos['creado_por'] = $request->user()?->id;
        $datos['actualizado_por'] = $request->user()?->id;
        unset($datos['estado']);

        if ($request->hasFile('imagen')) {
            $datos['imagen_id'] = $this->guardarMedio($request)->id;
        }

        $noticia = Noticia::create($datos);
        $this->generarIaAlGuardar($noticia, $estadoCodigo, contenidoCambio: true);

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia creada correctamente.');
    }

    /**
     * Muestra una noticia individual.
     */
    public function show(Noticia $noticia): View
    {
        $noticia->load(['categoria', 'autor', 'estado', 'medio']);

        return view('noticias.show', compact('noticia'));
    }

    /**
     * Muestra el formulario para editar una noticia.
     */
    public function edit(Noticia $noticia): View
    {
        $noticia->load('medio');
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $autores = Autor::where('activo', true)
            ->orWhere('id', $noticia->autor_id)
            ->orderBy('nombre')
            ->get();

        return view('noticias.edit', compact('noticia', 'categorias', 'autores'));
    }

    /**
     * Actualiza una noticia existente.
     */
    public function update(Request $request, Noticia $noticia): RedirectResponse
    {
        $noticia->load('medio');

        $tendraImagen = $request->hasFile('imagen')
            || ($noticia->imagen_id !== null && ! $request->boolean('quitar_imagen'));

        $datos = array_merge($this->validar($request, $noticia->id, $tendraImagen), $this->camposIa($request));
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Noticia::class, $noticia->id);
        $datos['destacada'] = $request->boolean('destacada');
        $estadoCodigo = $datos['estado'];
        $datos['estado_id'] = $this->estadoId($estadoCodigo);
        $datos['actualizado_por'] = $request->user()?->id;
        unset($datos['estado']);

        $medioAnterior = $noticia->medio;

        $contenidoCambio = $noticia->titulo !== ($datos['titulo'] ?? $noticia->titulo)
            || $noticia->contenido !== ($datos['contenido'] ?? $noticia->contenido);

        if ($request->hasFile('imagen')) {
            $datos['imagen_id'] = $this->guardarMedio($request)->id;
        } elseif ($request->boolean('quitar_imagen')) {
            $datos['imagen_id'] = null;
        } elseif ($medioAnterior !== null && $request->exists('imagen_alt')) {
            $medioAnterior->update(['texto_alternativo' => $request->input('imagen_alt')]);
        }

        $noticia->update($datos);
        $this->generarIaAlGuardar($noticia, $estadoCodigo, $contenidoCambio);

        if ($medioAnterior !== null && $medioAnterior->id !== $noticia->imagen_id) {
            $this->limpiarMedioSubido($medioAnterior);
        }

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia actualizada correctamente.');
    }

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

    /**
     * Elimina una noticia.
     */
    public function destroy(Noticia $noticia): RedirectResponse
    {
        $noticia->load('medio');
        $medio = $noticia->medio;

        $noticia->delete();
        $this->limpiarMedioSubido($medio);

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia eliminada correctamente.');
    }

    /**
     * Guarda la imagen subida en el disco público y registra el medio.
     *
     * El prefijo `storage/` permite usar `asset()` en las vistas públicas sin
     * tocar nada: la carpeta `public/storage` es un enlace al disco `public`.
     */
    private function guardarMedio(Request $request): Medio
    {
        $archivo = $request->file('imagen');
        $ruta = $archivo->store('imagenes/noticias', 'public');

        return Medio::create([
            'subido_por' => $request->user()?->id,
            'disco' => 'public',
            'ruta' => 'storage/'.$ruta,
            'tipo_mime' => $archivo->getMimeType() ?: Medio::mimeDe($ruta),
            'tamano_bytes' => max(1, (int) $archivo->getSize()),
            'texto_alternativo' => $request->input('imagen_alt'),
        ]);
    }

    /**
     * Elimina el archivo y el registro de un medio subido por el panel.
     *
     * Las imágenes demo que viven en `public/imagenes` (sin prefijo `storage/`)
     * se conservan, ya que no pertenecen al disco administrable y pueden ser
     * compartidas. Solo se borra el medio si ningún otro registro lo usa.
     */
    private function limpiarMedioSubido(?Medio $medio): void
    {
        if ($medio === null || ! str_starts_with($medio->ruta, 'storage/')) {
            return;
        }

        if ($medio->noticias()->exists() || $medio->eventos()->exists()) {
            return;
        }

        Storage::disk('public')->delete(Str::after($medio->ruta, 'storage/'));
        $medio->delete();
    }

    /**
     * Resuelve el id del estado de noticia a partir de su codigo.
     */
    private function estadoId(string $codigo): int
    {
        return (int) EstadoNoticia::where('codigo', $codigo)->value('id');
    }

    /**
     * Extrae y normaliza los campos generados por la IA presentes en la petición.
     *
     * Solo se incluyen los campos que llegan realmente, para no sobrescribir
     * valores existentes en peticiones parciales (por ejemplo, en los tests).
     *
     * @return array<string, mixed>
     */
    private function camposIa(Request $request): array
    {
        $datos = [];

        if ($request->exists('puntos_clave')) {
            $datos['puntos_clave'] = $this->aLista((string) $request->input('puntos_clave'), "\n");
        }

        if ($request->exists('etiquetas_ia')) {
            $datos['etiquetas_ia'] = array_slice($this->aLista((string) $request->input('etiquetas_ia'), ','), 0, 12);
        }

        return $datos;
    }

    /**
     * Convierte un texto separado por saltos de línea o comas en una lista limpia.
     *
     * @return array<int, string>
     */
    private function aLista(string $valor, string $separador): array
    {
        $partes = $separador === "\n"
            ? (preg_split('/\r\n|\r|\n/', $valor) ?: [])
            : explode($separador, $valor);

        $partes = array_filter(array_map('trim', $partes), fn (string $p) => $p !== '');

        return array_values(array_unique($partes));
    }

    /**
     * Genera con IA el embedding, el resumen y las etiquetas de una noticia.
     *
     * Nunca interrumpe el guardado: cualquier fallo del proveedor se registra
     * y la noticia se conserva tal y como la escribió la persona.
     */
    private function generarIaAlGuardar(Noticia $noticia, string $estadoCodigo, bool $contenidoCambio): void
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

    /**
     * Reglas de validación para crear y actualizar noticias.
     */
    private function validar(Request $request, ?int $idActual = null, bool $tendraImagen = false): array
    {
        $reglas = [
            'titulo' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'autor_id' => ['required', 'integer', 'exists:autores,id'],
            'resumen' => ['nullable', 'string', 'max:500'],
            'contenido' => ['required', 'string'],
            'imagen' => ['nullable'],
            'imagen_alt' => [$tendraImagen ? 'required' : 'nullable', 'string', 'max:255'],
            'quitar_imagen' => ['sometimes', 'boolean'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['borrador', 'publicada', 'archivada'])],
            'destacada' => ['sometimes', 'boolean'],
            'puntos_clave' => ['nullable', 'string'],
            'etiquetas_ia' => ['nullable', 'string'],
        ];

        if ($request->hasFile('imagen')) {
            $reglas['imagen'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'];
        } else {
            $reglas['imagen'] = ['nullable', 'string', 'max:2048'];
        }

        if ($idActual !== null) {
            $reglas['slug'] = ['nullable', 'string', 'max:255', Rule::unique('noticias', 'slug')->ignore($idActual)];
        }

        $mensajes = [
            'imagen_alt.required' => 'El texto alternativo es obligatorio cuando la noticia tiene una imagen.',
            'imagen_alt.max' => 'El texto alternativo no puede superar los 255 caracteres.',
        ];

        return $request->validate($reglas, $mensajes);
    }
}
