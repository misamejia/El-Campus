@props(['color' => 'verde'])

<span {{ $attributes->merge([
    'class' => 'inline-block px-2.5 py-1 font-sans text-[11px] font-semibold uppercase tracking-[0.14em] ' . match ($color) {
        'verde' => 'bg-verde-800 text-papel',
        'oro' => 'bg-oro-500 text-verde-950',
        'clara' => 'bg-verde-100 text-verde-800',
        default => 'bg-neutral-200 text-neutral-700',
    },
]) }}>
    {{ $slot }}
</span>