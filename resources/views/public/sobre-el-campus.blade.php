@extends('layouts.public')

@section('titulo', 'Sobre El Campus | El Campus')
@section('descripcion', 'Conoce El Campus, el periódico universitario digital de la Universidad de Córdoba: su propósito, secciones, contenido y equipo editorial.')

@section('contenido')
    <section aria-label="Sobre El Campus" class="mx-auto max-w-4xl">
        <x-titular etiqueta="Sobre nosotros">El Campus</x-titular>

        <h2 class="mt-10 font-display text-2xl font-bold text-neutral-900 md:text-3xl">¿Qué es El Campus?</h2>
        <p class="mt-4 leading-relaxed text-neutral-700">
            El Campus es el periódico universitario digital de la Universidad de Córdoba, Colombia.
            Es un medio pensado para reunir, difundir y preservar la información que nace de la vida
            universitaria: lo que ocurre en las aulas, los laboratorios, los escenarios deportivos y
            los espacios culturales de la institución.
        </p>

        <h2 class="mt-10 font-display text-2xl font-bold text-neutral-900 md:text-3xl">Propósito</h2>
        <p class="mt-4 leading-relaxed text-neutral-700">
            El objetivo de El Campus es servir a la comunidad universitaria de Montería y de todo el
            departamento de Córdoba, presentando información institucional, académica y cultural con
            un compromiso editorial claro: dar voz a estudiantes, docentes, egresados y a todos los
            actores que construyen la vida de la universidad.
        </p>

        <h2 class="mt-10 font-display text-2xl font-bold text-neutral-900 md:text-3xl">Secciones</h2>
        <p class="mt-4 leading-relaxed text-neutral-700">
            La navegación del periódico organiza el contenido en las siguientes secciones:
        </p>
        <ul class="mt-4 space-y-2 text-neutral-700">
            <li class="flex gap-2"><span class="text-oro-600" aria-hidden="true">•</span><a href="{{ route('seccion.universidad') }}" class="underline decoration-oro-500 underline-offset-4 hover:text-verde-800">Universidad</a>: noticias institucionales, académicas y de la comunidad universitaria.</li>
            <li class="flex gap-2"><span class="text-oro-600" aria-hidden="true">•</span><a href="{{ route('seccion.investigacion') }}" class="underline decoration-oro-500 underline-offset-4 hover:text-verde-800">Investigación</a>: ciencia, innovación y proyectos académicos.</li>
            <li class="flex gap-2"><span class="text-oro-600" aria-hidden="true">•</span><a href="{{ route('seccion.cultura') }}" class="underline decoration-oro-500 underline-offset-4 hover:text-verde-800">Cultura</a>: arte, literatura, música y patrimonio.</li>
            <li class="flex gap-2"><span class="text-oro-600" aria-hidden="true">•</span><a href="{{ route('seccion.deportes') }}" class="underline decoration-oro-500 underline-offset-4 hover:text-verde-800">Deportes</a>: actividad deportiva y vida saludable.</li>
            <li class="flex gap-2"><span class="text-oro-600" aria-hidden="true">•</span><a href="{{ route('seccion.opinion') }}" class="underline decoration-oro-500 underline-offset-4 hover:text-verde-800">Opinión</a>: columnas y análisis de la comunidad.</li>
            <li class="flex gap-2"><span class="text-oro-600" aria-hidden="true">•</span><a href="{{ route('eventos.publica') }}" class="underline decoration-oro-500 underline-offset-4 hover:text-verde-800">Eventos</a>: agenda y acontecimientos universitarios.</li>
        </ul>

        <h2 class="mt-10 font-display text-2xl font-bold text-neutral-900 md:text-3xl">Contenido</h2>
        <p class="mt-4 leading-relaxed text-neutral-700">
            El sitio reúne noticias, eventos y contenidos de interés universitario. Las noticias se
            publican por categorías y se pueden consultar desde la portada, desde cada sección o
            mediante el buscador del periódico.
        </p>

        <h2 class="mt-10 font-display text-2xl font-bold text-neutral-900 md:text-3xl">Equipo editorial</h2>
        <p class="mt-4 leading-relaxed text-neutral-700">
            Equipo editorial de El Campus. El periódico cuenta además con equipos de sección
            (redacción, investigación, cultura, deportes, eventos, admisiones y biblioteca) que
            preparan los contenidos firmados en cada publicación.
        </p>

        <h2 class="mt-10 font-display text-2xl font-bold text-neutral-900 md:text-3xl">Contacto</h2>
        <p class="mt-4 leading-relaxed text-neutral-700">
            Redacción El Campus<br>
            Campus Universitario de Berástegui, Montería, Córdoba, Colombia
        </p>
    </section>
@endsection