@props([
    'tipo' => 'normal',
    'noticia' => null,
    'titulo' => '',
    'categoria' => '',
    'fecha' => '',
    'resumen' => '',
    'autor' => '',
    'imagen' => '',
    'imagenAlt' => '',
    'enlace' => '#',
])

@php
    if ($noticia) {
        $titulo = $noticia->titulo;
        $categoria = $noticia->categoria?->nombre ?? '';
        $fecha = $noticia->fecha_publicacion?->isoFormat('D [de] MMMM [de] YYYY') ?? '';
        $resumen = $noticia->resumen ?? '';
        $autor = $noticia->autor?->nombre ?? '';
        $imagen = $noticia->imagen ?? '';
        $imagenAlt = $noticia->imagen_alt ?: $titulo;
        $enlace = route('noticia.publica', $noticia->slug);
    }

    $imagenAlt = $imagenAlt ?: $titulo;
@endphp

@if ($tipo === 'destacada')
    <article {{ $attributes->merge(['class' => 'group']) }}>
        <a href="{{ $enlace }}" class="block">
            @if ($imagen)
                <div class="overflow-hidden bg-verde-900">
                    <img src="{{ asset($imagen) }}" alt="{{ $imagenAlt }}" class="aspect-[16/9] w-full object-cover transition-transform duration-500 group-hover:scale-[1.02]">
                </div>
            @endif
            <div class="mt-5">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                    @if ($categoria)
                        <x-etiqueta color="oro">{{ $categoria }}</x-etiqueta>
                    @endif
                    @if ($fecha)
                        <span class="font-sans text-xs uppercase tracking-wider text-neutral-500">{{ $fecha }}</span>
                    @endif
                </div>
                <h3 class="mt-3 font-display text-2xl font-bold leading-tight text-neutral-900 break-words transition-colors group-hover:text-verde-800 md:text-4xl">
                    {{ $titulo }}
                </h3>
                @if ($resumen)
                    <p class="mt-3 max-w-3xl leading-relaxed text-neutral-600">{{ $resumen }}</p>
                @endif
                @if ($autor)
                    <p class="mt-3 font-sans text-sm font-medium text-neutral-500">Por {{ $autor }}</p>
                @endif
            </div>
        </a>
    </article>
@elseif ($tipo === 'lista')
    <article {{ $attributes }}>
        <a href="{{ $enlace }}" class="group flex gap-4">
            @if ($imagen)
                <div class="w-24 shrink-0 overflow-hidden bg-verde-900 md:w-28">
                    <img src="{{ asset($imagen) }}" alt="{{ $imagenAlt }}" class="h-20 w-full object-cover transition-opacity group-hover:opacity-90 md:h-24">
                </div>
            @endif
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    @if ($categoria)
                        <x-etiqueta color="clara">{{ $categoria }}</x-etiqueta>
                    @endif
                    @if ($fecha)
                        <span class="font-sans text-xs text-neutral-500">{{ $fecha }}</span>
                    @endif
                </div>
                <h3 class="mt-1 font-display text-base font-bold leading-snug text-neutral-900 break-words transition-colors group-hover:text-verde-800 md:text-lg">
                    {{ $titulo }}
                </h3>
                @if ($autor)
                    <p class="mt-1 font-sans text-xs text-neutral-500">Por {{ $autor }}</p>
                @endif
            </div>
        </a>
    </article>
@else
    <article {{ $attributes->merge(['class' => 'group']) }}>
        <a href="{{ $enlace }}" class="block">
            @if ($imagen)
                <div class="overflow-hidden bg-verde-900">
                    <img src="{{ asset($imagen) }}" alt="{{ $imagenAlt }}" class="aspect-[16/10] w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                </div>
            @endif
            <div class="mt-3">
                @if ($categoria)
                    <x-etiqueta color="oro">{{ $categoria }}</x-etiqueta>
                @endif
                <h3 class="mt-2 font-display text-lg font-bold leading-snug text-neutral-900 break-words transition-colors group-hover:text-verde-800 md:text-xl">
                    {{ $titulo }}
                </h3>
                @if ($resumen)
                    <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $resumen }}</p>
                @endif
                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 font-sans text-xs text-neutral-500">
                    @if ($fecha)
                        <span>{{ $fecha }}</span>
                    @endif
                    @if ($autor)
                        <span>Por {{ $autor }}</span>
                    @endif
                </div>
            </div>
        </a>
    </article>
@endif