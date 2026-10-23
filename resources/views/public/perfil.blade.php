@extends('layouts.public')

@section('titulo', 'Mi perfil — El Campus')

@section('descripcion', 'Mi perfil de usuario en El Campus, periódico universitario de la Universidad de Córdoba.')

@section('contenido')
    @php $seccion = 'Mi cuenta'; @endphp

    <p class="font-sans text-xs uppercase tracking-[0.3em] text-oro-700 font-semibold">Zona protegida de prueba</p>

    <h1 class="mt-2 font-display text-3xl font-black text-verde-950 md:text-4xl">
        Mi perfil
    </h1>

    <div class="mt-1 h-1 w-24 bg-oro-500" aria-hidden="true"></div>

    <div class="mt-10 grid gap-8 md:grid-cols-3">
        <div class="rounded-lg border border-neutral-200 bg-marfil p-6">
            <h2 class="font-sans text-xs font-bold uppercase tracking-[0.2em] text-neutral-400">Usuario</h2>
            <p class="mt-3 font-display text-2xl font-bold text-verde-800">{{ auth()->user()->name }}</p>
        </div>

        <div class="rounded-lg border border-neutral-200 bg-marfil p-6">
            <h2 class="font-sans text-xs font-bold uppercase tracking-[0.2em] text-neutral-400">Correo electrónico</h2>
            <p class="mt-3 font-display text-2xl font-bold break-all text-verde-800">{{ auth()->user()->email }}</p>
        </div>

        <div class="rounded-lg border border-neutral-200 bg-marfil p-6">
            <h2 class="font-sans text-xs font-bold uppercase tracking-[0.2em] text-neutral-400">Verificación</h2>
            <p class="mt-3 font-display text-2xl font-bold">
                @if (auth()->user()->hasVerifiedEmail())
                    <span class="text-verde-700">Verificado</span>
                @else
                    <span class="text-oro-700">Pendiente</span>
                @endif
            </p>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <x-boton href="{{ auth()->user()->isLector() ? route('profile.show') : route('dashboard') }}">Ir a mi cuenta</x-boton>
        <x-boton variante="secundario" href="{{ route('profile.show') }}">Configuración del perfil</x-boton>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-block px-5 py-2.5 font-sans text-sm font-semibold uppercase tracking-[0.12em] text-red-700 transition-colors hover:bg-red-50">
                Cerrar sesión
            </button>
        </form>
    </div>
@endsection