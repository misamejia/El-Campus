<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Acceso no autorizado') }} — {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-serif antialiased bg-papel text-neutral-800">
        <div class="min-h-screen flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-lg text-center">
                <a href="{{ route('inicio') }}" class="group mb-8 text-center no-underline block">
                    <span class="block text-[10px] uppercase tracking-[0.35em] text-oro-700 font-sans font-semibold">Universidad de Córdoba</span>
                    <span class="block font-display text-4xl font-black text-verde-800">El Campus</span>
                    <span class="block text-xs uppercase tracking-[0.3em] text-neutral-500 font-sans mt-1">Periódico Universitario</span>
                </a>

                <p class="font-sans text-xs uppercase tracking-[0.3em] text-oro-700 font-semibold">Error 403</p>
                <h1 class="mt-2 font-display text-4xl font-black text-verde-800">Acceso no autorizado</h1>
                <p class="mt-4 font-sans text-sm leading-relaxed text-neutral-600">
                    No tienes permisos para acceder a esta sección.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    @php
                        $tieneAccesoPanel = auth()->check() && (auth()->user()->isAdministrador() || auth()->user()->isEditor());
                    @endphp
                    <a href="{{ $tieneAccesoPanel ? route('dashboard') : route('inicio') }}" class="inline-block px-5 py-2.5 bg-verde-800 text-papel font-sans text-sm font-semibold uppercase tracking-[0.12em] transition-colors duration-200 hover:bg-verde-700">
                        {{ $tieneAccesoPanel ? 'Volver al panel' : 'Volver al inicio' }}
                    </a>
                    <a href="{{ route('inicio') }}" class="inline-block px-5 py-2.5 border border-verde-800 text-verde-800 font-sans text-sm font-semibold uppercase tracking-[0.12em] transition-colors duration-200 hover:bg-verde-50">
                        Ver el periódico
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>