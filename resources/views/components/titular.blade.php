@props(['etiqueta' => null])

<div {{ $attributes->merge(['class' => 'mb-8']) }}>
    @if ($etiqueta)
        <span class="font-sans text-xs font-semibold uppercase tracking-[0.18em] text-oro-900">{{ $etiqueta }}</span>
    @endif
    <div class="mt-1 border-t-4 border-verde-800 pt-2">
        <h2 class="font-display text-2xl font-bold leading-tight text-neutral-900 md:text-3xl">{{ $slot }}</h2>
    </div>
    <div class="mt-2 h-px bg-neutral-300"></div>
</div>