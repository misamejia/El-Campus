@extends('layouts.public')

@section('titulo', ($evento->titulo ?? 'Evento') . ' | El Campus')
@section('descripcion', \Illuminate\Support\Str::limit($evento->descripcion ?? 'Evento universitario de El Campus.', 160, '…'))

@section('contenido')
    <article class="mx-auto max-w-3xl">
        <div class="flex flex-wrap items-center gap-3">
            @php
                $etiquetaEstado = match ($evento->estado?->codigo) {
                    'programado' => ['texto' => 'Programado', 'color' => 'oro'],
                    'realizado' => ['texto' => 'Realizado', 'color' => 'clara'],
                    default => ['texto' => 'Evento', 'color' => 'default'],
                };
            @endphp
            <x-etiqueta :color="$etiquetaEstado['color']">{{ $etiquetaEstado['texto'] }}</x-etiqueta>
            <span class="font-sans text-xs uppercase tracking-wider text-neutral-500">
                {{ $evento->fecha_inicio?->isoFormat('D [de] MMMM [de] YYYY, h:mm a') }}
            </span>
        </div>

        <h1 class="mt-5 font-display text-3xl font-black leading-tight text-verde-950 md:text-5xl">
            {{ $evento->titulo }}
        </h1>

        <dl class="mt-6 grid gap-x-8 gap-y-3 border-y border-neutral-200 py-5 font-sans text-sm text-neutral-700 sm:grid-cols-2">
            <div class="flex items-start gap-2">
                <dt class="w-24 shrink-0 font-semibold uppercase tracking-wider text-neutral-500">Fecha</dt>
                <dd>
                    {{ $evento->fecha_inicio->isoFormat('D [de] MMMM [de] YYYY') }}
                    @if ($evento->fecha_fin && $evento->fecha_fin->isSameDay($evento->fecha_inicio))
                        <span class="text-neutral-500">· {{ $evento->fecha_inicio->isoFormat('h:mm a') }} a {{ $evento->fecha_fin->isoFormat('h:mm a') }}</span>
                    @elseif ($evento->fecha_fin)
                        <span class="text-neutral-500">· {{ $evento->fecha_inicio->isoFormat('h:mm a') }}</span>
                        <br><span class="text-neutral-500">hasta el {{ $evento->fecha_fin->isoFormat('D [de] MMMM [de] YYYY, h:mm a') }}</span>
                    @else
                        <span class="text-neutral-500">· {{ $evento->fecha_inicio->isoFormat('h:mm a') }}</span>
                    @endif
                </dd>
            </div>
            <div class="flex items-start gap-2">
                <dt class="w-24 shrink-0 font-semibold uppercase tracking-wider text-neutral-500">Lugar</dt>
                <dd>{{ $evento->lugar }}</dd>
            </div>
            @if ($evento->organizador)
                <div class="flex items-start gap-2">
                    <dt class="w-24 shrink-0 font-semibold uppercase tracking-wider text-neutral-500">Organiza</dt>
                    <dd>{{ $evento->organizador->nombre }}</dd>
                </div>
            @endif
        </dl>

        @if ($evento->imagen)
            <div class="mt-8 overflow-hidden">
                <img src="{{ asset($evento->imagen) }}" alt="{{ $evento->imagen_alt ?: $evento->titulo }}" class="aspect-[16/9] w-full object-cover">
            </div>
        @endif

        <div class="mt-8 space-y-4 font-serif text-lg leading-relaxed text-neutral-800">
            {!! nl2br(e($evento->descripcion)) !!}
        </div>

        <div class="mt-10 border-t border-neutral-200 pt-6">
            <x-boton href="{{ route('eventos.publica') }}">Volver a los eventos</x-boton>
        </div>
    </article>
@endsection