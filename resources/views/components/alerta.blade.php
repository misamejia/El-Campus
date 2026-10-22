@props(['tipo' => 'informacion'])

@php
    $estilos = match ($tipo) {
        'exito' => 'border-verde-600 bg-verde-50 text-verde-900',
        'error' => 'border-red-600 bg-red-50 text-red-900',
        'aviso' => 'border-oro-600 bg-oro-50 text-oro-900',
        default => 'border-neutral-400 bg-neutral-100 text-neutral-800',
    };
@endphp

<div role="alert" {{ $attributes->merge([
    'class' => 'border-l-4 px-4 py-3 font-sans text-sm leading-relaxed ' . $estilos,
]) }}>
    {{ $slot }}
</div>