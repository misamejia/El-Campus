@extends('layouts.public')

@section('titulo', 'Eventos | El Campus')
@section('descripcion', 'Agenda de eventos de la Universidad de Córdoba: ferias, seminarios, torneos, jornadas culturales y encuentros académicos abiertos a la comunidad universitaria.')

@section('contenido')
    <section aria-label="Presentación de eventos">
        <x-titular etiqueta="Agenda universitaria">Eventos</x-titular>
        <p class="max-w-3xl leading-relaxed text-neutral-600">
            En esta sección se reúne la agenda de la Universidad de Córdoba: ferias de ciencia,
            seminarios de investigación, torneos deportivos, jornadas de bienestar y encuentros
            culturales abiertos a la comunidad universitaria.
        </p>
    </section>

    <section aria-label="Próximos eventos" class="mt-12">
        <div class="mb-6 flex items-center justify-between border-b-2 border-neutral-300 pb-2">
            <h2 class="font-display text-xl font-bold text-neutral-900">Próximos eventos</h2>
        </div>

        <div class="grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($proximos as $evento)
                <x-tarjeta-evento :evento="$evento" />
            @empty
                <div class="md:col-span-2 lg:col-span-3">
                    <x-alerta>Próximamente se publicarán nuevos eventos universitarios.</x-alerta>
                </div>
            @endforelse
        </div>
    </section>

    <section aria-label="Eventos realizados" class="mt-12">
        <div class="mb-6 flex items-center justify-between border-b-2 border-neutral-300 pb-2">
            <h2 class="font-display text-xl font-bold text-neutral-900">Eventos realizados</h2>
        </div>

        <div class="grid grid-cols-1 gap-7 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($realizados as $evento)
                <x-tarjeta-evento :evento="$evento" />
            @empty
                <div class="md:col-span-2 lg:col-span-3">
                    <x-alerta>No hay eventos realizados para mostrar.</x-alerta>
                </div>
            @endforelse
        </div>
    </section>
@endsection