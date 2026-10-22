@extends('layouts.public')

@php
    $rutasSeccion = [
        'universidad' => route('seccion.universidad'),
        'investigacion' => route('seccion.investigacion'),
        'cultura' => route('seccion.cultura'),
        'deportes' => route('seccion.deportes'),
        'opinion' => route('seccion.opinion'),
        'eventos' => route('eventos.publica'),
    ];
@endphp

@section('titulo', 'Inicio | El Campus')
@section('descripcion', 'Portada de El Campus, el periódico universitario digital de la Universidad de Córdoba, Colombia: investigación, cultura, deportes y vida universitaria.')

@section('contenido')
    <section aria-label="Noticias principales" class="grid gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($principal)
                <x-tarjeta-noticia tipo="destacada" :noticia="$principal" />
            @else
                <div class="border border-neutral-200 bg-marfil p-8 text-center">
                    <p class="font-sans text-sm text-neutral-500">Todavía no hay noticias publicadas.</p>
                </div>
            @endif
        </div>

        <aside class="flex flex-col gap-8">
            @foreach ($secundarias as $noticia)
                <x-tarjeta-noticia :noticia="$noticia" />
            @endforeach
        </aside>
    </section>

    <section aria-label="Últimas noticias" class="mt-14">
        <x-titular>Últimas noticias</x-titular>
        <div class="grid gap-x-10 gap-y-8 md:grid-cols-2">
            @forelse ($ultimas as $noticia)
                <x-tarjeta-noticia tipo="lista" :noticia="$noticia" />
            @empty
                <p class="font-sans text-sm text-neutral-500">No hay publicaciones recientes.</p>
            @endforelse
        </div>
    </section>

    <section aria-label="Noticias por categoría" class="mt-14">
        <x-titular>Explora las secciones del periódico</x-titular>

        <div class="grid grid-cols-1 gap-x-10 gap-y-12 md:grid-cols-2">
            @foreach ($porCategorias as $bloque)
                <div>
                    <div class="mb-5 flex items-center justify-between border-b-2 border-neutral-300 pb-2">
                        <h3 class="font-display text-xl font-bold text-neutral-900">{{ $bloque['categoria']->nombre }}</h3>
                        <x-boton href="{{ $rutasSeccion[$bloque['categoria']->slug] ?? '#' }}" variante="fantasma">Ver sección</x-boton>
                    </div>
                    <div class="grid grid-cols-1 gap-7 sm:grid-cols-2">
                        @foreach ($bloque['noticias'] as $noticia)
                            <x-tarjeta-noticia :noticia="$noticia" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section aria-label="Sobre El Campus" class="mt-14">
        <div class="border border-neutral-300 bg-marfil p-6 md:p-8">
            <x-titular etiqueta="Sobre nosotros">El Campus, la voz de nuestra universidad</x-titular>
            <p class="max-w-4xl leading-relaxed text-neutral-600">
                El Campus es el periódico universitario digital de la Universidad de Córdoba. Informamos
                sobre lo que ocurre en las aulas, los laboratorios, los escenarios deportivos y culturales
                de la institución, con el compromiso editorial de servir a la comunidad universitaria
                de Montería y de todo el departamento.
            </p>
            <div class="mt-6">
                <x-boton href="{{ route('sobre-el-campus') }}" variante="secundario">Leer más sobre El Campus</x-boton>
            </div>
        </div>
    </section>

    <div class="mt-14">
        @include('public.partials.nota-redaccion')
    </div>
@endsection