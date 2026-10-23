<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\View\View;

class EventoPublicController extends Controller
{
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
}
