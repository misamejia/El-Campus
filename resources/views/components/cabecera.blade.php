@props(['activa' => null])

@php
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fechaHoy = ucfirst($dias[(int) date('w')]) . ', ' . (int) date('j') . ' de ' . $meses[(int) date('n') - 1] . ' de ' . date('Y');
    $tieneAccesoPanel = auth()->check() && (auth()->user()->isAdministrador() || auth()->user()->isEditor());
@endphp

<header class="bg-verde-800">
    <div class="border-b border-verde-700">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2 font-sans text-[11px] uppercase tracking-wider text-papel/75 md:text-xs">
            <span class="hidden sm:inline">{{ $fechaHoy }}</span>
            <span>Montería, Colombia</span>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline">Periódico Universitario</span>
                <span class="hidden sm:inline text-papel/40" aria-hidden="true">|</span>
                @auth
                    <span class="flex items-center gap-2">
                        <a href="{{ $tieneAccesoPanel ? route('dashboard') : route('perfil') }}" class="font-semibold text-papel hover:text-oro-400">Mi cuenta</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="hover:text-papel/70">Cerrar sesión</button>
                        </form>
                    </span>
                @else
                    <span class="flex items-center gap-4">
                        <a href="{{ route('login') }}" class="font-semibold text-papel hover:text-oro-400">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="border border-oro-500 bg-oro-500 px-3 py-1 font-semibold text-verde-950 transition-colors hover:bg-oro-600">Regístrate</a>
                    </span>
                @endauth
            </div>
        </div>
    </div>

    <div class="border-b border-verde-900">
        <div class="mx-auto max-w-7xl px-4 py-7 text-center md:py-9">
            <a href="{{ route('inicio') }}" class="inline-block">
                <span class="block font-display text-4xl font-black uppercase tracking-[0.18em] text-papel md:text-6xl">
                    El Campus
                </span>
            </a>
            <p class="mt-2 font-sans text-[11px] uppercase tracking-[0.28em] text-papel/70 md:text-xs md:tracking-[0.4em]">
                Periódico Universitario · Universidad de Córdoba
            </p>
            <div class="mx-auto mt-4 h-1 w-24 bg-oro-500" aria-hidden="true"></div>
        </div>
    </div>

    <x-navegacion :activa="$activa" />
</header>