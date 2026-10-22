<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=source-serif-4:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-serif antialiased bg-papel text-neutral-800">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-6 py-12">
            <a href="{{ route('inicio') }}" class="group mb-8 text-center no-underline">
                <span class="block text-[10px] uppercase tracking-[0.35em] text-oro-700 font-sans font-semibold">Universidad de Córdoba</span>
                <span class="block font-display text-4xl font-black text-verde-800">El Campus</span>
                <span class="block text-xs uppercase tracking-[0.3em] text-neutral-500 font-sans mt-1">Periódico Universitario</span>
            </a>

            <div class="w-full sm:max-w-md">
                {{ $slot }}
            </div>

            <p class="mt-8 text-center">
                <a href="{{ route('inicio') }}" class="text-sm text-neutral-500 hover:text-verde-700 underline underline-offset-4">
                    &larr; Volver al inicio
                </a>
            </p>
        </div>

        @stack('modals')
        @livewireScripts
    </body>
</html>