@extends('layouts.public')

@section('titulo', ($noticia->titulo ?? 'Noticia') . ' | El Campus')
@section('descripcion', $noticia->resumen ?? 'Artículo de El Campus, el periódico universitario de la Universidad de Córdoba.')

@section('contenido')
    <article class="mx-auto max-w-3xl">
        <div class="flex flex-wrap items-center gap-3">
            @if ($noticia->categoria)
                <x-etiqueta color="oro">{{ $noticia->categoria->nombre }}</x-etiqueta>
            @endif
            <span class="font-sans text-xs uppercase tracking-wider text-neutral-500">
                {{ $noticia->fecha_publicacion?->isoFormat('D [de] MMMM [de] YYYY') ?: 'Sin fecha de publicación' }}
            </span>
        </div>

        <h1 class="mt-5 font-display text-3xl font-black leading-tight text-verde-950 md:text-5xl">
            {{ $noticia->titulo }}
        </h1>

        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-3">
            @if ($noticia->autor)
                <p class="font-sans text-sm font-medium text-neutral-600">
                    Por <strong class="font-semibold text-verde-800">{{ $noticia->autor->nombre }}</strong>
                </p>
            @endif

            <div id="controles-lectura" role="group" aria-label="Controles de lectura en voz alta" class="flex flex-wrap items-center gap-2">
                <button type="button" id="boton-escuchar"
                    class="inline-flex min-h-[44px] items-center gap-2 border border-verde-800 bg-verde-800 px-4 py-2 font-sans text-xs font-semibold uppercase tracking-wider text-papel transition-colors hover:bg-verde-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro-500 focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                    </svg>
                    <span data-etiqueta>Escuchar noticia</span>
                </button>

                <button type="button" id="boton-pausar" hidden
                    class="inline-flex min-h-[44px] items-center gap-2 border border-verde-800 bg-marfil px-4 py-2 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro-500 focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
                    </svg>
                    <span data-etiqueta-pausa>Pausar</span>
                </button>

                <button type="button" id="boton-detener" hidden
                    class="inline-flex min-h-[44px] items-center gap-2 border border-verde-800 bg-marfil px-4 py-2 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro-500 focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25h13.5v13.5H5.25z" />
                    </svg>
                    Detener
                </button>

                <label for="velocidad-lectura" class="font-sans text-xs font-semibold uppercase tracking-wider text-neutral-600">Velocidad</label>
                <select id="velocidad-lectura"
                    class="min-h-[44px] rounded border border-neutral-300 bg-marfil px-2 font-sans text-sm text-neutral-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro-500">
                    <option value="0.75">0,75×</option>
                    <option value="1" selected>1×</option>
                    <option value="1.25">1,25×</option>
                    <option value="1.5">1,5×</option>
                </select>
            </div>

            <p id="estado-lectura" role="status" aria-live="polite" class="sr-only"></p>
            <p id="lectura-no-soportada" class="hidden w-full font-sans text-xs text-neutral-500"></p>
        </div>

        @if ($noticia->imagen)
            <div class="mt-8 overflow-hidden">
                <img src="{{ asset($noticia->imagen) }}" alt="{{ $noticia->imagen_alt ?: $noticia->titulo }}" class="aspect-[16/9] w-full object-cover">
            </div>
        @endif

        @if ($noticia->resumen)
            <p class="mt-8 font-serif text-xl italic leading-relaxed text-neutral-700">
                {{ $noticia->resumen }}
            </p>
        @endif

        @if (! empty($noticia->puntos_clave))
            <aside class="mt-8 border-s-4 border-verde-700 bg-verde-50 p-5 sm:p-6">
                <h2 class="font-sans text-xs font-semibold uppercase tracking-wider text-verde-800">Claves de la noticia</h2>
                <ul class="mt-3 list-disc space-y-1.5 ps-5 font-sans text-base leading-relaxed text-neutral-700">
                    @foreach ($noticia->puntos_clave as $clave)
                        <li>{{ $clave }}</li>
                    @endforeach
                </ul>
            </aside>
        @endif

        <div class="mt-6 space-y-4 font-serif text-lg leading-relaxed text-neutral-800">
            {!! nl2br(e($noticia->contenido)) !!}
        </div>

        @if (! empty($noticia->etiquetas_ia))
            <div class="mt-10 flex flex-wrap gap-2 border-t border-neutral-200 pt-6">
                @foreach ($noticia->etiquetas_ia as $etiqueta)
                    <span class="inline-flex items-center rounded-full border border-neutral-300 bg-marfil px-3 py-1 font-sans text-xs uppercase tracking-wider text-neutral-600">
                        #{{ $etiqueta }}
                    </span>
                @endforeach
            </div>
        @endif
    </article>

    @if (isset($relacionadas) && $relacionadas->isNotEmpty())
        <section class="mx-auto mt-14 max-w-7xl border-t border-neutral-200 pt-8">
            <h2 class="font-display text-2xl font-bold text-verde-950 md:text-3xl">También te puede interesar</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($relacionadas as $relacionada)
                    <x-tarjeta-noticia :noticia="$relacionada" />
                @endforeach
            </div>
        </section>
    @endif

    <div class="mx-auto mt-10 max-w-3xl border-t border-neutral-200 pt-6">
        <x-boton href="{{ route('inicio') }}">Volver a la portada</x-boton>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const iniciar = document.getElementById('boton-escuchar');
        const pausar = document.getElementById('boton-pausar');
        const detener = document.getElementById('boton-detener');
        const velocidad = document.getElementById('velocidad-lectura');
        const estado = document.getElementById('estado-lectura');
        const grupo = document.getElementById('controles-lectura');
        const noSoportado = document.getElementById('lectura-no-soportada');

        if (!iniciar || !grupo) {
            return;
        }

        if (!('speechSynthesis' in window) || typeof SpeechSynthesisUtterance === 'undefined') {
            grupo.hidden = true;

            if (noSoportado) {
                noSoportado.classList.remove('hidden');
                noSoportado.textContent = 'Tu navegador no admite la lectura en voz alta. Puedes leer el artículo directamente en esta página.';
            }

            return;
        }

        const sintesis = window.speechSynthesis;
        const texto = @json(trim($noticia->titulo . '. ' . ($noticia->resumen ?? '') . '. ' . $noticia->contenido));
        const etiquetaPausa = pausar ? pausar.querySelector('[data-etiqueta-pausa]') : null;
        let hablando = false;
        let pausado = false;

        function anunciar(mensaje) {
            if (estado) {
                estado.textContent = mensaje;
            }
        }

        function visibilidad(activo) {
            iniciar.hidden = activo;
            if (pausar) { pausar.hidden = !activo; }
            if (detener) { detener.hidden = !activo; }
        }

        function vozEnEspanol() {
            const voces = sintesis.getVoices() || [];

            for (let i = 0; i < voces.length; i++) {
                if (/^es/i.test(voces[i].lang)) {
                    return voces[i];
                }
            }

            return null;
        }

        function crearLectura() {
            const lectura = new SpeechSynthesisUtterance(texto);
            const voz = vozEnEspanol();

            lectura.lang = voz ? voz.lang : 'es-CO';
            if (voz) { lectura.voice = voz; }
            lectura.rate = parseFloat(velocidad ? velocidad.value : '1') || 1;
            lectura.onend = finalizar;
            lectura.onerror = finalizar;

            return lectura;
        }

        function finalizar() {
            hablando = false;
            pausado = false;
            visibilidad(false);

            if (etiquetaPausa) { etiquetaPausa.textContent = 'Pausar'; }
            anunciar('Lectura detenida.');
        }

        function hablar() {
            sintesis.cancel();
            hablando = true;
            pausado = false;
            visibilidad(true);

            if (etiquetaPausa) { etiquetaPausa.textContent = 'Pausar'; }
            sintesis.speak(crearLectura());
            anunciar('Lectura en curso.');
        }

        iniciar.addEventListener('click', function () {
            if (!hablando) { hablar(); }
        });

        if (pausar) {
            pausar.addEventListener('click', function () {
                if (!hablando) { return; }

                if (pausado) {
                    sintesis.resume();
                    pausado = false;
                    if (etiquetaPausa) { etiquetaPausa.textContent = 'Pausar'; }
                    anunciar('Lectura reanudada.');
                } else {
                    sintesis.pause();
                    pausado = true;
                    if (etiquetaPausa) { etiquetaPausa.textContent = 'Reanudar'; }
                    anunciar('Lectura pausada.');
                }
            });
        }

        if (detener) {
            detener.addEventListener('click', function () {
                sintesis.cancel();
                finalizar();
            });
        }

        if (velocidad) {
            velocidad.addEventListener('change', function () {
                if (hablando) {
                    hablar();
                    anunciar('Velocidad de lectura actualizada.');
                }
            });
        }

        window.addEventListener('pagehide', function () {
            sintesis.cancel();
        });
    })();
</script>
@endpush
