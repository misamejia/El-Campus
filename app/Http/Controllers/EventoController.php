<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventoRequest;
use App\Http\Requests\UpdateEventoRequest;
use App\Models\EstadoEvento;
use App\Models\Evento;
use App\Models\Organizador;
use App\Services\Medios\GestorMedios;
use App\Support\SlugUnico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

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
    public function store(StoreEventoRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Evento::class);
        $datos['estado_id'] = $this->estadoId($datos['estado']);
        $datos['organizador_id'] = $this->organizadorId($datos['organizador'] ?? null);
        $datos['creado_por'] = $request->user()?->id;
        $datos['actualizado_por'] = $request->user()?->id;

        $rutaImagen = $datos['imagen'] ?? null;
        unset($datos['estado'], $datos['organizador'], $datos['imagen'], $datos['imagen_alt'], $datos['quitar_imagen']);

        $medio = null;

        try {
            DB::transaction(function () use ($request, &$datos, $rutaImagen, &$medio) {
                if ($request->hasFile('imagen')) {
                    $medio = $this->gestorMedios()->guardar($request, 'imagenes/eventos');
                    $datos['imagen_id'] = $medio->id;
                } else {
                    $datos['imagen_id'] = $this->gestorMedios()->desdeEntrada($request, $rutaImagen, 'imagenes/eventos');
                }

                Evento::create($datos);
            });
        } catch (Throwable $e) {
            $this->gestorMedios()->limpiar($medio);

            throw $e;
        }

        return redirect()->route('eventos.index')
            ->with('exito', 'Evento creado correctamente.');
    }

    /**
     * Muestra un evento individual.
     */
    public function show(Evento $evento): View
    {
        $evento->load(['estado', 'organizador', 'medio']);

        return view('eventos.show', compact('evento'));
    }

    /**
     * Muestra el formulario para editar un evento.
     */
    public function edit(Evento $evento): View
    {
        $evento->load('medio');

        return view('eventos.edit', compact('evento'));
    }

    /**
     * Actualiza un evento existente conservando la unicidad del slug.
     */
    public function update(UpdateEventoRequest $request, Evento $evento): RedirectResponse
    {
        $evento->load('medio');

        $datos = $request->validated();
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Evento::class, $evento->id);
        $datos['estado_id'] = $this->estadoId($datos['estado']);
        $datos['organizador_id'] = $this->organizadorId($datos['organizador'] ?? null);
        $datos['actualizado_por'] = $request->user()?->id;

        $medioAnterior = $evento->medio;
        $rutaImagen = $datos['imagen'] ?? null;
        unset($datos['estado'], $datos['organizador'], $datos['imagen'], $datos['imagen_alt'], $datos['quitar_imagen']);

        $medioNuevo = null;

        try {
            DB::transaction(function () use ($request, $evento, &$datos, $rutaImagen, &$medioNuevo) {
                if ($request->hasFile('imagen')) {
                    $medioNuevo = $this->gestorMedios()->guardar($request, 'imagenes/eventos');
                    $datos['imagen_id'] = $medioNuevo->id;
                } elseif ($request->boolean('quitar_imagen')) {
                    $datos['imagen_id'] = null;
                } elseif (! empty($rutaImagen)) {
                    $datos['imagen_id'] = $this->gestorMedios()->desdeEntrada($request, $rutaImagen, 'imagenes/eventos');
                }

                $evento->update($datos);
            });
        } catch (Throwable $e) {
            if ($medioNuevo !== null && $medioNuevo->id !== $medioAnterior?->id) {
                $this->gestorMedios()->limpiar($medioNuevo);
            }

            throw $e;
        }

        if ($medioAnterior !== null && $medioAnterior->id !== $evento->imagen_id) {
            $this->gestorMedios()->limpiar($medioAnterior);
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
    public function destroy(Evento $evento): RedirectResponse
    {
        $evento->load('medio');
        $medio = $evento->medio;

        $evento->delete();
        $this->gestorMedios()->limpiar($medio);

        return redirect()->route('eventos.index')
            ->with('exito', 'Evento eliminado correctamente.');
    }

    /**
     * Gestor de imágenes compartidas por el panel.
     */
    private function gestorMedios(): GestorMedios
    {
        return app(GestorMedios::class);
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
}
