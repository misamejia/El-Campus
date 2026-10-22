@extends('layouts.public')

@section('titulo', $categoria->nombre . ' | El Campus')
@section('descripcion', $categoria->descripcion ?: 'Sección de El Campus, el periódico universitario digital de la Universidad de Córdoba.')

@section('contenido')
    <section aria-label="Sección {{ $categoria->nombre }}">
        <x-titular etiqueta="Sección">{{ $categoria->nombre }}</x-titular>

        @if ($categoria->descripcion)
            <p class="max-w-3xl leading-relaxed text-neutral-600">{{ $categoria->descripcion }}</p>
        @endif

        @if ($noticias->isNotEmpty())
            <div class="mt-10 grid grid-cols-1 gap-x-10 gap-y-8 md:grid-cols-2">
                @foreach ($noticias as $noticia)
                    <x-tarjeta-noticia :noticia="$noticia" />
                @endforeach
            </div>
        @else
            <div class="mt-10 border border-neutral-200 bg-marfil p-8 text-center">
                <p class="font-sans text-sm text-neutral-500">Todavía no hay noticias publicadas en esta sección.</p>
            </div>
        @endif
    </section>
@endsection