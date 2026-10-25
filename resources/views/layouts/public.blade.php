<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('titulo', 'El Campus — Periódico Universitario de la Universidad de Córdoba')</title>
        <meta name="description" content="@yield('descripcion', 'El Campus, periódico universitario digital de la Universidad de Córdoba, Colombia: investigación, cultura, deportes y vida universitaria.')">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta property="og:site_name" content="El Campus">
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('titulo', 'El Campus')">
        <meta property="og:description" content="@yield('descripcion', 'El Campus — Periódico Universitario de la Universidad de Córdoba.')">

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col font-serif antialiased">
        @php $seccion = $seccion ?? null; @endphp

        <x-cabecera :activa="$seccion" />

        <main id="contenido" class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-10 md:py-12">
                @yield('contenido')
            </div>
        </main>

        <x-pie />

        @stack('scripts')
    </body>
</html>