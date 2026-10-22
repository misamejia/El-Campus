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
        $publicadas = Noticia::delEstado('publicada')->count();
        $borradores = Noticia::delEstado('borrador')->count();
        $archivadas = Noticia::delEstado('archivada')->count();

        $totalAutores = Autor::count();
        $totalCategorias = Categoria::count();

        $totalEventos = Evento::count();
        $programados = Evento::delEstado('programado')->count();
        $realizados = Evento::delEstado('realizado')->count();
        $cancelados = Evento::delEstado('cancelado')->count();

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