<?php

namespace App\Http\Controllers;

use App\Models\Autor;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\Noticia;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Panel administrativo central de El Campus.
     *
     * Obtiene las estadísticas generales (siempre desde la base de datos),
     * los estados de noticias y eventos, las últimas noticias registradas
     * y los próximos eventos programados. La lógica vive en el controlador;
     * la vista solo presenta los datos.
     */
    public function index(): View
    {
        $totalNoticias = Noticia::count();
        $conteoNoticias = Noticia::query()
            ->join('estados_noticia', 'estados_noticia.id', '=', 'noticias.estado_id')
            ->selectRaw('estados_noticia.codigo as codigo, count(*) as total')
            ->groupBy('estados_noticia.codigo')
            ->pluck('total', 'codigo');

        $publicadas = (int) ($conteoNoticias['publicada'] ?? 0);
        $borradores = (int) ($conteoNoticias['borrador'] ?? 0);
        $archivadas = (int) ($conteoNoticias['archivada'] ?? 0);

        $totalAutores = Autor::count();
        $totalCategorias = Categoria::count();

        $totalEventos = Evento::count();
        $conteoEventos = Evento::query()
            ->join('estados_evento', 'estados_evento.id', '=', 'eventos.estado_id')
            ->selectRaw('estados_evento.codigo as codigo, count(*) as total')
            ->groupBy('estados_evento.codigo')
            ->pluck('total', 'codigo');

        $programados = (int) ($conteoEventos['programado'] ?? 0);
        $realizados = (int) ($conteoEventos['realizado'] ?? 0);
        $cancelados = (int) ($conteoEventos['cancelado'] ?? 0);

        $noticiasRecientes = Noticia::with(['categoria', 'autor', 'estado', 'medio'])
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $proximosEventos = Evento::with(['estado', 'organizador', 'medio'])
            ->delEstado('programado')
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->take(5)
            ->get();

        return view('dashboard', [
            'totalNoticias' => $totalNoticias,
            'publicadas' => $publicadas,
            'borradores' => $borradores,
            'archivadas' => $archivadas,
            'totalAutores' => $totalAutores,
            'totalCategorias' => $totalCategorias,
            'totalEventos' => $totalEventos,
            'programados' => $programados,
            'realizados' => $realizados,
            'cancelados' => $cancelados,
            'noticiasRecientes' => $noticiasRecientes,
            'proximosEventos' => $proximosEventos,
        ]);
    }
}
