<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Autor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <article class="bg-white border border-neutral-200 shadow-sm p-8">
                <div class="flex flex-wrap items-center gap-2 font-sans text-xs uppercase tracking-widest text-neutral-500">
                    @if ($autor->activo)
                        <span class="rounded bg-green-100 px-2 py-1 font-semibold text-green-800">Activo</span>
                    @else
                        <span class="rounded bg-neutral-100 px-2 py-1 font-semibold text-neutral-600">Inactivo</span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-black text-verde-950 leading-tight">
                    {{ $autor->nombre }}
                </h1>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="font-sans text-xs uppercase tracking-widest text-neutral-500">Slug</dt>
                        <dd class="mt-1 font-mono text-sm text-neutral-700">{{ $autor->slug }}</dd>
                    </div>
                    <div>
                        <dt class="font-sans text-xs uppercase tracking-widest text-neutral-500">Email</dt>
                        <dd class="mt-1 text-sm text-neutral-700">{{ $autor->email ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-sans text-xs uppercase tracking-widest text-neutral-500">Descripción</dt>
                        <dd class="mt-1 text-sm text-neutral-700">{{ $autor->descripcion ?: '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-sans text-xs uppercase tracking-widest text-neutral-500">Noticias publicadas</dt>
                        <dd class="mt-1 text-sm text-neutral-700">{{ $autor->noticias->count() }}</dd>
                    </div>
                </dl>

                @if ($autor->noticias->isNotEmpty())
                    <h2 class="mt-8 font-display text-lg font-bold text-verde-900">
                        Noticias asociadas
                    </h2>
                    <ul class="mt-3 divide-y divide-neutral-100 border border-neutral-200">
                        @foreach ($autor->noticias as $noticia)
                            <li class="flex items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <a href="{{ route('noticias.show', $noticia) }}" class="font-serif text-sm font-semibold text-neutral-800 hover:text-verde-700">
                                        {{ $noticia->titulo }}
                                    </a>
                                    <div class="mt-0.5 text-xs text-neutral-500">
                                        {{ $noticia->categoria?->nombre }} · {{ $noticia->fecha_publicacion?->format('d/m/Y') ?: 'Sin fecha' }}
                                    </div>
                                </div>
                                <span class="shrink-0 inline-block rounded px-2 py-0.5 font-sans text-xs font-semibold capitalize bg-papel text-neutral-600">{{ $noticia->estado?->nombre }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-8 text-sm text-neutral-500">
                        Este autor todavía no tiene noticias asociadas.
                    </p>
                @endif
            </article>

            <div class="mt-6 flex items-center gap-3">
                <x-boton href="{{ route('autores.index') }}">Volver al listado</x-boton>
                <x-boton variante="secundario" href="{{ route('autores.edit', $autor) }}">Editar autor</x-boton>
            </div>
        </div>
    </div>
</x-app-layout>