@props([
    'titulo' => '',
    'valor' => 0,
    'descripcion' => null,
    'enlace' => null,
])

@php
    $clases = $enlace
        ? 'group flex h-full flex-col justify-between rounded-lg border border-neutral-200 bg-marfil p-6 shadow-sm transition-colors duration-200 hover:border-verde-400'
        : 'flex h-full flex-col justify-between rounded-lg border border-neutral-200 bg-marfil p-6 shadow-sm';
@endphp

@if ($enlace)
    <a href="{{ $enlace }}" {{ $attributes->merge(['class' => $clases]) }}>
@else
    <div {{ $attributes->merge(['class' => $clases]) }}>
@endif
    <div>
        <p class="font-sans text-xs font-semibold uppercase tracking-widest text-neutral-500">{{ $titulo }}</p>
        @if ($descripcion)
                <p class="mt-1 text-sm text-neutral-600">{{ $descripcion }}</p>
        @endif
        <p class="sr-only">Total de {{ $titulo }}: {{ $valor }}</p>
    </div>
    <div class="mt-6 flex items-end justify-between gap-4">
        <span class="font-display text-4xl font-black text-verde-800">{{ $valor }}</span>
        @if ($enlace)
            <span class="font-sans text-xs font-semibold uppercase tracking-wider text-verde-700 transition-colors group-hover:text-verde-500">
                Ver →
            </span>
        @endif
    </div>
@if ($enlace)
    </a>
@else
    </div>
@endif