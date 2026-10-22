@props(['href' => '#', 'variante' => 'primario'])

@php
    $clases = match ($variante) {
        'primario' => 'bg-oro-500 text-verde-950 hover:bg-oro-600',
        'secundario' => 'border border-verde-800 text-verde-800 hover:bg-verde-50',
        'fantasma' => 'text-verde-800 underline decoration-oro-500 underline-offset-4 hover:text-verde-700',
        default => 'bg-neutral-900 text-papel hover:bg-neutral-700',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge([
    'class' => 'inline-block px-5 py-2.5 font-sans text-sm font-semibold uppercase tracking-[0.12em] transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-verde-800/50 focus-visible:ring-offset-2 ' . $clases,
]) }}>
    {{ $slot }}
</a>