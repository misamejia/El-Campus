<?php

namespace App\Http\Controllers;

use App\Models\EstadoEvento;
use App\Models\Evento;
use App\Models\Medio;
use App\Models\Organizador;
use App\Support\SlugUnico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventoController extends Controller
{
    /**
     * Lista los eventos ordenados por su fecha de inicio (los próximos primero).
     */
    public function index(): View
    {
        $eventos = Evento::with(['estado', 'organizador', 'medio'])
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->get();

        return view('eventos.index', compact('eventos'));
    }

    /**
     * Muestra el formulario para crear un evento.
     */
    public function create(): View
    {
        return view('eventos.create');
    }

    /**
     * Almacena un evento nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request, null, $request->hasFile('imagen') || $request->filled('imagen'));
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Evento::class);
        $datos['estado_id'] = $this->estadoId($datos['estado']);
        $datos['organizador_id'] = $this->organizadorId($datos['organizador'] ?? null);
        $datos['imagen_id'] = $this->imagenIdDesdeEntrada($request, $datos['imagen'] ?? null);
        $datos['creado_por'] = $request->user()?->id;
        $datos['actualizado_por'] = $request->user()?->id;
        unset($datos['estado'], $datos['organizador'], $datos['imagen']);

        Evento::create($datos);

        return redirect()->route('eventos.index')
            ->with('exito', 'Evento creado correctamente.');
    }

    /**
     * Muestra un evento individual.
     */
    public function show(string $evento): View
    {
        $evento = Evento::with(['estado', 'organizador', 'medio'])->findOrFail($evento);

        return view('eventos.show', compact('evento'));
    }

    /**
     * Muestra el formulario para editar un evento.
     */
    public function edit(string $evento): View
    {
        $evento = Evento::with('medio')->findOrFail($evento);

        return view('eventos.edit', compact('evento'));
    }

    /**
     * Actualiza un evento existente conservando la unicidad del slug.
     */
    public function update(Request $request, string $evento): RedirectResponse
    {
        $evento = Evento::with('medio')->findOrFail($evento);

        $datos = $this->validar($request, $evento->id, $request->hasFile('imagen') || $request->filled('imagen'));
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Evento::class, $evento->id);
        $datos['estado_id'] = $this->estadoId($datos['estado']);
        $datos['organizador_id'] = $this->organizadorId($datos['organizador'] ?? null);
        $datos['actualizado_por'] = $request->user()?->id;
        unset($datos['estado'], $datos['organizador']);

        $medioAnterior = $evento->medio;

        if ($request->hasFile('imagen')) {
            $datos['imagen_id'] = $this->guardarMedio($request)->id;
        } elseif ($request->boolean('quitar_imagen')) {
            $datos['imagen_id'] = null;
        } elseif (! empty($datos['imagen'])) {
            $datos['imagen_id'] = $this->imagenIdDesdeEntrada($request, $datos['imagen']);
        }

        unset($datos['imagen']);

        $evento->update($datos);

        if ($medioAnterior !== null && $medioAnterior->id !== $evento->imagen_id) {
            $this->limpiarMedioSubido($medioAnterior);
        }

        return redirect()->route('eventos.index')
            ->with('exito', 'Evento actualizado correctamente.');
    }

    /**
     * Portada pública de eventos.
     *
     * Solo se muestran eventos programados (los próximos primero) y realizados
     * (los más recientes primero). Los cancelados no tienen presencia pública.
     * Son dos colecciones ordenadas de forma "editorial": primero lo que viene.
     */
    public function publicas(): View
    {
        $proximos = Evento::with(['estado', 'organizador', 'medio'])
            ->delEstado('programado')
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->get();

        $realizados = Evento::with(['estado', 'organizador', 'medio'])
            ->delEstado('realizado')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        return view('public.eventos', [
            'seccion' => 'eventos',
            'proximos' => $proximos,
            'realizados' => $realizados,
        ]);
    }

    /**
     * Evento público individual.
     *
     * Busca por slug y únicamente permite eventos públicamente visibles
     * (programados o realizados). Los cancelados y los slugs inexistentes
     * producen 404 (firstOrFail).
     */
    public function publico(string $slug): View
    {
        $evento = Evento::with(['estado', 'organizador', 'medio'])
            ->where('slug', $slug)
            ->publicos()
            ->firstOrFail();

        return view('public.evento', [
            'seccion' => 'eventos',
            'evento' => $evento,
        ]);
    }

    /**
     * Elimina un evento.
     */
    public function destroy(string $evento): RedirectResponse
    {
        $evento = Evento::with('medio')->findOrFail($evento);
        $medio = $evento->medio;

        $evento->delete();
        $this->limpiarMedioSubido($medio);

        return redirect()->route('eventos.index')
            ->with('exito', 'Evento eliminado correctamente.');
    }

    /**
     * Guarda la imagen subida en el disco público y registra el medio.
     */
    private function guardarMedio(Request $request): Medio
    {
        $archivo = $request->file('imagen');
        $ruta = $archivo->store('imagenes/eventos', 'public');

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
     * Devuelve el id de un medio a partir de la entrada del formulario:
     * un archivo subido o una ruta de texto. Null si no hay imagen.
     */
    private function imagenIdDesdeEntrada(Request $request, ?string $ruta): ?int
    {
        if ($request->hasFile('imagen')) {
            return $this->guardarMedio($request)->id;
        }

        $medio = Medio::desdeRuta($ruta, $request->user()?->id);

        if ($medio !== null && $request->exists('imagen_alt')) {
            $medio->update(['texto_alternativo' => $request->input('imagen_alt')]);
        }

        return $medio?->id;
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
     * Resuelve el id del estado de evento a partir de su codigo.
     */
    private function estadoId(string $codigo): int
    {
        return (int) EstadoEvento::where('codigo', $codigo)->value('id');
    }

    /**
     * Resuelve (o crea) el organizador a partir de su nombre.
     */
    private function organizadorId(?string $nombre): ?int
    {
        $nombre = $nombre !== null ? trim($nombre) : '';

        if ($nombre === '') {
            return null;
        }

        return Organizador::firstOrCreate(['nombre' => $nombre], ['activo' => true])->id;
    }

    /**
     * Reglas de validación para crear y actualizar eventos.
     */
    private function validar(Request $request, ?int $idActual = null, bool $tendraImagen = false): array
    {
        $reglas = [
            'titulo' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('eventos', 'slug')->ignore($idActual)],
            'descripcion' => ['required', 'string'],
            'lugar' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'imagen' => ['nullable'],
            'imagen_alt' => [$tendraImagen ? 'required' : 'nullable', 'string', 'max:255'],
            'quitar_imagen' => ['sometimes', 'boolean'],
            'organizador' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['programado', 'realizado', 'cancelado'])],
        ];

        if ($request->hasFile('imagen')) {
            $reglas['imagen'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'];
        } else {
            $reglas['imagen'] = ['nullable', 'string', 'max:2048'];
        }

        $mensajes = [
            'titulo.required' => 'El título del evento es obligatorio.',
            'titulo.max' => 'El título no puede superar los 255 caracteres.',
            'slug.unique' => 'Ya existe un evento con ese slug.',
            'descripcion.required' => 'La descripción del evento es obligatoria.',
            'lugar.required' => 'El lugar del evento es obligatorio.',
            'lugar.max' => 'El lugar no puede superar los 255 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'imagen.max' => 'La ruta de la imagen no puede superar los 2048 caracteres.',
            'imagen_alt.required' => 'El texto alternativo es obligatorio cuando el evento tiene una imagen.',
            'imagen_alt.max' => 'El texto alternativo no puede superar los 255 caracteres.',
            'organizador.max' => 'El organizador no puede superar los 255 caracteres.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];

        return $request->validate($reglas, $mensajes);
    }
}
