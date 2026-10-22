<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <article class="bg-white border border-neutral-200 shadow-sm p-8">
                @php
                    $estadoEstilos = [
                        'programado' => 'bg-verde-100 text-verde-800',
                        'realizado' => 'bg-oro-100 text-oro-900',
                        'cancelado' => 'bg-red-100 text-red-800',
                    ];
                    $estadoEtiquetas = [
                        'programado' => 'Programado',
                        'realizado' => 'Realizado',
                        'cancelado' => 'Cancelado',
                    ];
                @endphp

                <div class="flex flex-wrap gap-2 font-sans text-xs uppercase tracking-widest text-neutral-500">
                    <span class="rounded px-2 py-1 font-semibold {{ $estadoEstilos[$evento->estado?->codigo] ?? 'bg-neutral-100' }}">{{ $estadoEtiquetas[$evento->estado?->codigo] ?? ucfirst($evento->estado?->codigo ?? '') }}</span>
                </div>

                <h1 class="mt-4 font-display text-3xl font-black text-verde-950 leading-tight">
                    {{ $evento->titulo }}
                </h1>

                <dl class="mt-5 grid gap-3 font-sans text-sm text-neutral-600 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-neutral-400">Fecha de inicio</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $evento->fecha_inicio?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-neutral-400">Fecha de fin</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $evento->fecha_fin?->format('d/m/Y H:i') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-neutral-400">Lugar</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $evento->lugar }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-widest text-neutral-400">Organizador</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $evento->organizador?->nombre ?: '—' }}</dd>
                    </div>
                </dl>

                @if ($evento->imagen)
                    <div class="mt-6">
                        <img src="{{ asset($evento->imagen) }}" alt="{{ $evento->imagen_alt ?: $evento->titulo }}" class="w-full object-cover">
                    </div>
                @endif

                <div class="mt-6 space-y-4 font-serif text-base leading-relaxed text-neutral-800">
                    {!! nl2br(e($evento->descripcion)) !!}
                </div>
            </article>

            <div class="mt-6 flex items-center gap-3">
                <x-boton href="{{ route('eventos.index') }}">Volver al listado</x-boton>
                <x-boton variante="secundario" href="{{ route('eventos.edit', $evento) }}">Editar evento</x-boton>
            </div>
        </div>
    </div>
</x-app-layout>