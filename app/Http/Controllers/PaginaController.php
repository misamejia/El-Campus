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
        $relaciones = ['categoria', 'autor', 'estado', 'medio'];

        $principal = Noticia::with($relaciones)
            ->delEstado('publicada')
            ->where('destacada', true)
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->first()
            ?? Noticia::with($relaciones)
                ->delEstado('publicada')
                ->orderByDesc('fecha_publicacion')
                ->orderByDesc('id')
                ->first();

        $secundarias = Noticia::with($relaciones)
            ->delEstado('publicada')
            ->when($principal, fn ($consulta) => $consulta->whereKeyNot($principal->getKey()))
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $ocupados = collect([$principal?->getKey()])
            ->merge($secundarias->modelKeys())
            ->filter()
            ->values();

        $ultimas = Noticia::with($relaciones)
            ->delEstado('publicada')
            ->when($ocupados->isNotEmpty(), fn ($consulta) => $consulta->whereKeyNot($ocupados->all()))
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        $porCategorias = Categoria::where('activa', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(function (Categoria $categoria) use ($relaciones) {
                $noticias = Noticia::with($relaciones)
                    ->delEstado('publicada')
                    ->where('categoria_id', $categoria->id)
                    ->orderByDesc('fecha_publicacion')
                    ->orderByDesc('id')
                    ->limit(2)
                    ->get();

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
