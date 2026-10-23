<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BusquedaController extends Controller
{
    /**
     * Buscador público de El Campus.
     *
     * Consulta únicamente contenido publicado: noticias en estado "publicada"
     * y eventos en estado "programado" o "realizado". El término se normaliza
     * (trim y espacios colapsados) y se usa con LIKE mediante el Query Builder,
     * por lo que los parámetros siempre van vinculados, nunca interpolados.
     * Los comodines % y _ se escapan para evitar búsquedas amplias accidentales.
     * No se ejecuta ninguna consulta si el término está vacío.
     */
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $termino = trim($datos['q'] ?? '');
        $termino = preg_replace('/\s+/u', ' ', $termino) ?? '';

        $noticias = new Collection;
        $eventos = new Collection;

        if ($termino !== '') {
            $terminoEscapado = addcslashes($termino, '\\%_');
            $like = '%'.$terminoEscapado.'%';
            $escape = '\\';

            $noticias = Noticia::with(['categoria', 'autor', 'estado', 'medio'])
                ->delEstado('publicada')
                ->where(function ($consulta) use ($like, $escape) {
                    $consulta->whereRaw('titulo LIKE ? ESCAPE ?', [$like, $escape])
                        ->orWhereRaw('resumen LIKE ? ESCAPE ?', [$like, $escape])
                        ->orWhereRaw('contenido LIKE ? ESCAPE ?', [$like, $escape])
                        ->orWhereHas('categoria', fn ($categoria) => $categoria->whereRaw('nombre LIKE ? ESCAPE ?', [$like, $escape]))
                        ->orWhereHas('autor', fn ($autor) => $autor->whereRaw('nombre LIKE ? ESCAPE ?', [$like, $escape]));
                })
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('id')
                ->limit(10)
                ->get();

            $eventos = Evento::with(['estado', 'organizador', 'medio'])
                ->publicos()
                ->where(function ($consulta) use ($like, $escape) {
                    $consulta->whereRaw('titulo LIKE ? ESCAPE ?', [$like, $escape])
                        ->orWhereRaw('descripcion LIKE ? ESCAPE ?', [$like, $escape])
                        ->orWhereRaw('lugar LIKE ? ESCAPE ?', [$like, $escape])
                        ->orWhereHas('organizador', fn ($organizador) => $organizador->whereRaw('nombre LIKE ? ESCAPE ?', [$like, $escape]));
                })
                ->orderByRaw("CASE WHEN (SELECT codigo FROM estados_evento WHERE estados_evento.id = eventos.estado_id) = 'programado' THEN 0 ELSE 1 END")
                ->orderBy('fecha_inicio')
                ->orderBy('id')
                ->limit(10)
                ->get();
        }

        return view('public.buscar', [
            'seccion' => 'buscar',
            'termino' => $termino,
            'noticias' => $noticias,
            'eventos' => $eventos,
        ]);
    }
}
