<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Noticia;
use Illuminate\View\View;

class PaginaController extends Controller
{
    /**
     * Portada dinámica del periódico.
     *
     * Todos los datos provienen de la base de datos mediante Eloquent,
     * con eager loading de las relaciones categoria y autor (sin N+1).
     * Solo se consideran noticias publicadas.
     */
    public function inicio(): View
    {
        $publicadas = Noticia::with(['categoria', 'autor', 'estado', 'medio'])
            ->delEstado('publicada')
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->get();

        $principal = $publicadas->firstWhere('destacada', true)
            ?? $publicadas->first();

        $secundarias = $publicadas
            ->where('id', '!==', $principal?->id)
            ->take(3)
            ->values();

        $ocupados = collect([$principal?->id, ...$secundarias->pluck('id')])->filter();

        $ultimas = $publicadas
            ->whereNotIn('id', $ocupados)
            ->take(4)
            ->values();

        $porCategorias = Categoria::where('activa', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(function (Categoria $categoria) use ($publicadas) {
                $noticias = $publicadas
                    ->where('categoria_id', $categoria->id)
                    ->take(2)
                    ->values();

                return $noticias->isEmpty() ? null : [
                    'categoria' => $categoria,
                    'noticias' => $noticias,
                ];
            })
            ->filter()
            ->values();

        return view('public.inicio', [
            'seccion' => 'inicio',
            'principal' => $principal,
            'secundarias' => $secundarias,
            'ultimas' => $ultimas,
            'porCategorias' => $porCategorias,
        ]);
    }

    /**
     * Página informativa de Sobre El Campus.
     *
     * No es una categoría de noticias: presenta el medio, su propósito,
     * sus secciones, el contenido, el equipo editorial y el contacto.
     */
    public function sobreElCampus(): View
    {
        return view('public.sobre-el-campus', [
            'seccion' => 'sobre-el-campus',
        ]);
    }

    public function perfil()
    {
        return view('public.perfil');
    }
}