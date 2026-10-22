@props([
    'evento' => null,
    'titulo' => '',
    'estado' => '',
    'fecha' => '',
    'lugar' => '',
    'organizador' => '',
    'imagen' => '',
    'imagenAlt' => '',
    'enlace' => '#',
])

@php
    if ($evento) {
        $titulo = $evento->titulo;
        $estado = $evento->estado?->codigo ?? '';
        $fecha = $evento->fecha_inicio?->isoFormat('D [de] MMMM [de] YYYY, h:mm a') ?? '';
        $lugar = $evento->lugar;
        $organizador = $evento->organizador?->nombre ?? '';
        $imagen = $evento->imagen ?? '';
        $imagenAlt = $evento->imagen_alt ?: $titulo;
        $enlace = route('evento.publica', $evento->slug);
    }

    $imagenAlt = $imagenAlt ?: $titulo;

    $etiquetaEstado = match ($estado) {
        'programado' => ['texto' => 'Programado', 'color' => 'verde'],
        'realizado' => ['texto' => 'Realizado', 'color' => 'clara'],
        'cancelado' => ['texto' => 'Cancelado', 'color' => 'default'],
        default => ['texto' => 'Evento', 'color' => 'default'],
    };
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col border border-neutral-200 bg-marfil transition-colors duration-200 hover:border-verde-300']) }}>
    <a href="{{ $enlace }}" class="flex h-full flex-col">
        @if ($imagen)
            <div class="overflow-hidden bg-verde-900">
                <img src="{{ asset($imagen) }}" alt="{{ $imagenAlt }}" class="aspect-[16/10] w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
            </div>
        @endif

        <div class="flex flex-1 flex-col p-5">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                <x-etiqueta :color="$etiquetaEstado['color']">{{ $etiquetaEstado['texto'] }}</x-etiqueta>
                @if ($fecha)
                    <span class="font-sans text-xs uppercase tracking-wider text-neutral-500">{{ $fecha }}</span>
                @endif
            </div>

            <h3 class="mt-3 font-display text-lg font-bold leading-snug text-neutral-900 break-words transition-colors group-hover:text-verde-800 md:text-xl">
                {{ $titulo }}
            </h3>

            <div class="mt-auto pt-5">
                @if ($lugar)
                    <p class="font-sans text-sm text-neutral-600">
                        <span class="font-semibold text-neutral-800">Dónde:</span> {{ $lugar }}
                    </p>
                @endif
                @if ($organizador)
                    <p class="mt-1 font-sans text-sm text-neutral-600">
                        <span class="font-semibold text-neutral-800">Organiza:</span> {{ $organizador }}
                    </p>
                @endif
            </div>
        </div>
    </a>
</article>