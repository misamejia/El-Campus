@props(['activa' => null])

@php
    $secciones = [
        'inicio' => ['nombre' => 'Inicio', 'url' => route('inicio')],
        'universidad' => ['nombre' => 'Universidad', 'url' => route('seccion.universidad')],
        'investigacion' => ['nombre' => 'Investigación', 'url' => route('seccion.investigacion')],
        'cultura' => ['nombre' => 'Cultura', 'url' => route('seccion.cultura')],
        'deportes' => ['nombre' => 'Deportes', 'url' => route('seccion.deportes')],
        'eventos' => ['nombre' => 'Eventos', 'url' => route('eventos.publica')],
        'opinion' => ['nombre' => 'Opinión', 'url' => route('seccion.opinion')],
        'sobre-el-campus' => ['nombre' => 'Sobre El Campus', 'url' => route('sobre-el-campus')],
        'buscar' => ['nombre' => 'Buscar', 'url' => route('buscar')],
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'border-t border-neutral-200 bg-papel']) }} aria-label="Navegación principal">
    <div class="mx-auto max-w-7xl px-4">
        <div class="md:flex md:items-center">
            <button
                type="button"
                data-menu-boton
                aria-controls="menu-principal"
                aria-expanded="false"
                class="flex w-full items-center justify-between border-b border-neutral-200 py-3 font-sans text-sm font-semibold uppercase tracking-wider text-neutral-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-verde-800/40 md:hidden">
                <span>Secciones</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <ul id="menu-principal" data-menu-contenido class="hidden md:flex md:flex-wrap md:items-center md:justify-center md:divide-x md:divide-neutral-200">
                @foreach ($secciones as $slug => $seccion)
                    <li class="border-b border-neutral-200 last:border-b-0 md:border-0">
                        <a
                            href="{{ $seccion['url'] }}"
                            @if ($activa === $slug) aria-current="page" @endif
                            class="{{ $activa === $slug ? 'text-verde-800 underline decoration-2 decoration-oro-500 underline-offset-8' : 'text-neutral-700 hover:text-verde-700' }} block px-4 py-3 font-sans text-xs font-semibold uppercase tracking-[0.14em] transition-colors md:text-sm">
                            {{ $seccion['nombre'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>