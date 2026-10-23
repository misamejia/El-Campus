<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticiaRequest;
use App\Http\Requests\UpdateNoticiaRequest;
use App\Models\Autor;
use App\Models\Categoria;
use App\Models\EstadoNoticia;
use App\Models\Noticia;
use App\Services\Medios\GestorMedios;
use App\Services\Noticias\GeneradorIa;
use App\Support\SlugUnico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->paginate(15);

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
    public function store(StoreNoticiaRequest $request): RedirectResponse
    {
        $datos = array_merge($request->validated(), $this->camposIa($request));
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Noticia::class);
        $datos['destacada'] = $request->boolean('destacada');
        $estadoCodigo = $datos['estado'];
        $datos['estado_id'] = $this->estadoId($estadoCodigo);
        $datos['creado_por'] = $request->user()?->id;
        $datos['actualizado_por'] = $request->user()?->id;
        unset($datos['estado'], $datos['imagen'], $datos['imagen_alt'], $datos['quitar_imagen']);

        $medio = null;

        try {
            $noticia = DB::transaction(function () use ($request, &$datos, &$medio) {
                if ($request->hasFile('imagen')) {
                    $medio = $this->gestorMedios()->guardar($request, 'imagenes/noticias');
                    $datos['imagen_id'] = $medio->id;
                }

                return Noticia::create($datos);
            });
        } catch (Throwable $e) {
            $this->gestorMedios()->limpiar($medio);

            throw $e;
        }

        $this->generadorIa()->generar($noticia, $estadoCodigo, contenidoCambio: true);

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia creada correctamente.');
    }

    /**
     * Muestra una noticia individual.
     */
    public function show(Noticia $noticia): View
    {
        $noticia->load([
            'categoria',
            'autor',
            'estado',
            'medio',
            'creadoPor',
            'actualizadoPor',
            'historialEstados.estadoAnterior',
            'historialEstados.estadoNuevo',
            'historialEstados.usuario',
        ]);

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
    public function update(UpdateNoticiaRequest $request, Noticia $noticia): RedirectResponse
    {
        $noticia->load('medio');

        $datos = array_merge($request->validated(), $this->camposIa($request));
        $datos['slug'] = SlugUnico::generar($request->input('titulo'), Noticia::class, $noticia->id);
        $datos['destacada'] = $request->boolean('destacada');
        $estadoCodigo = $datos['estado'];
        $datos['estado_id'] = $this->estadoId($estadoCodigo);
        $datos['actualizado_por'] = $request->user()?->id;
        unset($datos['estado'], $datos['imagen'], $datos['imagen_alt'], $datos['quitar_imagen']);

        $medioAnterior = $noticia->medio;

        $contenidoCambio = $noticia->titulo !== ($datos['titulo'] ?? $noticia->titulo)
            || $noticia->contenido !== ($datos['contenido'] ?? $noticia->contenido);

        $medioNuevo = null;

        try {
            DB::transaction(function () use ($request, $noticia, &$datos, $medioAnterior, &$medioNuevo) {
                if ($request->hasFile('imagen')) {
                    $medioNuevo = $this->gestorMedios()->guardar($request, 'imagenes/noticias');
                    $datos['imagen_id'] = $medioNuevo->id;
                } elseif ($request->boolean('quitar_imagen')) {
                    $datos['imagen_id'] = null;
                } elseif ($medioAnterior !== null && $request->exists('imagen_alt')) {
                    $this->gestorMedios()->actualizarTextoAlternativo($medioAnterior, $request);
                }

                $noticia->update($datos);
            });
        } catch (Throwable $e) {
            if ($medioNuevo !== null && $medioNuevo->id !== $medioAnterior?->id) {
                $this->gestorMedios()->limpiar($medioNuevo);
            }

            throw $e;
        }

        $this->generadorIa()->generar($noticia, $estadoCodigo, $contenidoCambio);

        if ($medioAnterior !== null && $medioAnterior->id !== $noticia->imagen_id) {
            $this->gestorMedios()->limpiar($medioAnterior);
        }

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia actualizada correctamente.');
    }

    /**
     * Elimina una noticia.
     */
    public function destroy(Noticia $noticia): RedirectResponse
    {
        $noticia->load('medio');
        $medio = $noticia->medio;

        $noticia->delete();
        $this->gestorMedios()->limpiar($medio);

        return redirect()->route('noticias.index')
            ->with('exito', 'Noticia eliminada correctamente.');
    }

    /**
     * Gestor de imágenes compartidas por el panel.
     */
    private function gestorMedios(): GestorMedios
    {
        return app(GestorMedios::class);
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
     * Generador de los campos de IA de la noticia.
     */
    private function generadorIa(): GeneradorIa
    {
        return app(GeneradorIa::class);
    }
}
