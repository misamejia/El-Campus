@extends('layouts.public')

@section('titulo', 'Buscar | El Campus')
@section('descripcion', 'Busca noticias y eventos de El Campus, el periódico universitario de la Universidad de Córdoba: investigación, cultura, deportes y vida universitaria.')

@section('contenido')
    <section aria-label="Búsqueda en El Campus">
        <x-titular :etiqueta="$termino !== '' ? 'Resultados de búsqueda' : 'Toda la información de la universidad'">
            @if ($termino !== '')
                Resultados para: {{ $termino }}
            @else
                Buscar en El Campus
            @endif
        </x-titular>

        <p class="max-w-3xl leading-relaxed text-neutral-600">
            Encuentra noticias y eventos de la Universidad de Córdoba: investigaciones,
            conferencias, cultura, deportes y toda la vida universitaria.
        </p>

        <form
            method="GET"
            action="{{ route('buscar') }}"
            role="search"
            class="mt-8 flex max-w-3xl flex-col gap-3 sm:flex-row"
        >
            <label for="q" class="sr-only">Buscar noticias, eventos, autores...</label>
            <input
                type="search"
                id="q"
                name="q"
                value="{{ old('q', $termino) }}"
                placeholder="Buscar noticias, eventos, autores..."
                maxlength="100"
                class="w-full flex-1 border border-neutral-300 bg-marfil px-4 py-3 font-sans text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-verde-800 focus:outline-none focus:ring-2 focus:ring-verde-800/20"
            >
            <button
                type="submit"
                class="inline-block shrink-0 bg-oro-500 px-5 py-3 font-sans text-sm font-semibold uppercase tracking-[0.12em] text-verde-950 transition-colors duration-200 hover:bg-oro-600 focus:outline-none focus:ring-2 focus:ring-oro-500/50 focus:ring-offset-2"
            >
                Buscar
            </button>
        </form>

        @error('q')
            <div class="mt-4 max-w-3xl">
                <x-alerta tipo="error">{{ $message }}</x-alerta>
            </div>
        @enderror
    </section>

    @if ($termino !== '')
        @if ($noticias->isEmpty() && $eventos->isEmpty())
            <section aria-label="Sin resultados" class="mt-12">
                <x-alerta>No encontramos resultados para tu búsqueda.</x-alerta>
                <p class="mt-4 font-sans text-sm text-neutral-500">
                    Prueba con otro término: por ejemplo, el nombre de un tema, una categoría o un autor.
                </p>
            </section>
        @else
            @if ($noticias->isNotEmpty())
                <section id="resultados-noticias" aria-label="Noticias encontradas" class="mt-12">
                    <div class="mb-6 flex items-center justify-between border-b-2 border-neutral-300 pb-2">
                        <h2 class="font-display text-xl font-bold text-neutral-900">Noticias</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($noticias as $noticia)
                            <x-tarjeta-noticia :noticia="$noticia" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($eventos->isNotEmpty())
                <section id="resultados-eventos" aria-label="Eventos encontrados" class="mt-12">
                    <div class="mb-6 flex items-center justify-between border-b-2 border-neutral-300 pb-2">
                        <h2 class="font-display text-xl font-bold text-neutral-900">Eventos</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($eventos as $evento)
                            <x-tarjeta-evento :evento="$evento" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    @else
        <section aria-label="Sugerencias de búsqueda" class="mt-12 max-w-3xl">
            <h2 class="font-display text-xl font-bold text-neutral-900">¿Qué estás buscando?</h2>
            <p class="mt-3 leading-relaxed text-neutral-600">
                Escribe una palabra o frase, por ejemplo <strong>investigación</strong>,
                <strong>deportes</strong>, <strong>cultura</strong>, <strong>semillero</strong>,
                <strong>conferencia</strong>, <strong>biblioteca</strong> o <strong>Córdoba</strong>,
                y encuentra las noticias y los eventos publicados por El Campus.
            </p>
        </section>
    @endif
@endsection