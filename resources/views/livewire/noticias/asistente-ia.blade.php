<div x-data class="border border-verde-200 bg-verde-50 p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-verde-800">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
            </svg>
            <h3 class="font-display text-lg font-bold">Asistente de redacción</h3>
        </div>

        @unless ($real)
            <span class="inline-flex items-center rounded-full border border-oro-300 bg-oro-50 px-3 py-1 font-sans text-[11px] font-semibold uppercase tracking-wider text-oro-800">
                Modo demostración
            </span>
        @endunless
    </div>

    <p class="mt-2 font-sans text-xs text-neutral-600">
        Rellena el titular y el contenido y deja que la IA te ayude. Los textos se insertan en el formulario y puedes editarlos antes de guardar.
        @unless ($real)
            <span class="mt-1 block text-neutral-500">Sin <code>GEMINI_API_KEY</code>: los resultados son aproximados (heurística local).</span>
        @endunless
    </p>

    <div class="mt-4 flex flex-wrap gap-2">
        <button type="button"
            x-on:click="$wire.generarResumen(document.getElementById('titulo')?.value ?? '', document.getElementById('contenido')?.value ?? '')"
            wire:loading.attr="disabled" wire:target="generarResumen"
            class="inline-flex items-center gap-1.5 border border-verde-300 bg-white px-3 py-1.5 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-100 disabled:opacity-50">
            Generar resumen
        </button>

        <button type="button"
            x-on:click="$wire.sugerirTitulares(document.getElementById('titulo')?.value ?? '', document.getElementById('contenido')?.value ?? '')"
            wire:loading.attr="disabled" wire:target="sugerirTitulares"
            class="inline-flex items-center gap-1.5 border border-verde-300 bg-white px-3 py-1.5 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-100 disabled:opacity-50">
            Sugerir titulares
        </button>

        <button type="button"
            x-on:click="$wire.sugerirEtiquetas(document.getElementById('titulo')?.value ?? '', document.getElementById('contenido')?.value ?? '')"
            wire:loading.attr="disabled" wire:target="sugerirEtiquetas"
            class="inline-flex items-center gap-1.5 border border-verde-300 bg-white px-3 py-1.5 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-100 disabled:opacity-50">
            Sugerir etiquetas
        </button>

        <button type="button"
            x-on:click="$wire.revisarEstilo(document.getElementById('titulo')?.value ?? '', document.getElementById('contenido')?.value ?? '')"
            wire:loading.attr="disabled" wire:target="revisarEstilo"
            class="inline-flex items-center gap-1.5 border border-verde-300 bg-white px-3 py-1.5 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-100 disabled:opacity-50">
            Revisar estilo
        </button>

        <span wire:loading wire:target="generarResumen,sugerirTitulares,sugerirEtiquetas,revisarEstilo"
            class="inline-flex items-center font-sans text-xs text-neutral-500">Generando…</span>
    </div>

    @if ($error)
        <div class="mt-4 border border-red-300 bg-red-50 p-3 font-sans text-sm text-red-700">{{ $error }}</div>
    @endif

    @if ($resumen !== null)
        <section class="mt-5 border-t border-verde-200 pt-4">
            <h4 class="font-sans text-xs font-semibold uppercase tracking-wider text-neutral-500">Resumen sugerido</h4>
            <p class="mt-1 font-serif text-base text-neutral-800">{{ $resumen }}</p>

            @if (! empty($puntos))
                <p class="mt-3 font-sans text-xs font-semibold uppercase tracking-wider text-neutral-500">Puntos clave</p>
                <ul class="mt-1 list-disc space-y-1 ps-5 font-sans text-sm text-neutral-700">
                    @foreach ($puntos as $punto)
                        <li>{{ $punto }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    @if (! empty($titulares))
        <section class="mt-5 border-t border-verde-200 pt-4">
            <h4 class="font-sans text-xs font-semibold uppercase tracking-wider text-neutral-500">Titulares alternativos</h4>
            <ul class="mt-2 space-y-1">
                @foreach ($titulares as $titular)
                    <li>
                        <button type="button" data-titulo="{{ $titular }}"
                            x-on:click="const el = document.getElementById('titulo'); if (el) { el.value = $el.dataset.titulo; el.dispatchEvent(new Event('input', { bubbles: true })); }"
                            class="w-full border border-transparent px-2 py-1 text-left font-serif text-base text-verde-900 transition-colors hover:border-verde-300 hover:bg-verde-100">
                            {{ $titular }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (! empty($etiquetas))
        <section class="mt-5 border-t border-verde-200 pt-4">
            <h4 class="font-sans text-xs font-semibold uppercase tracking-wider text-neutral-500">Etiquetas sugeridas</h4>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($etiquetas as $etiqueta)
                    <span class="inline-flex items-center rounded-full border border-neutral-300 bg-marfil px-3 py-1 font-sans text-xs text-neutral-700">{{ $etiqueta }}</span>
                @endforeach
            </div>
            <button type="button" wire:click="aplicarEtiquetas"
                class="mt-3 inline-flex items-center border border-verde-300 bg-white px-3 py-1.5 font-sans text-xs font-semibold uppercase tracking-wider text-verde-800 transition-colors hover:bg-verde-100">
                Usar estas etiquetas
            </button>
        </section>
    @endif

    @if ($estiloResumen !== null)
        <section class="mt-5 border-t border-verde-200 pt-4">
            <h4 class="font-sans text-xs font-semibold uppercase tracking-wider text-neutral-500">Revisión de estilo</h4>
            <p class="mt-1 font-sans text-sm text-neutral-700">{{ $estiloResumen }}</p>
            <ul class="mt-2 space-y-1 font-sans text-sm text-neutral-700">
                @foreach ($estilo as $sugerencia)
                    <li><strong class="font-semibold text-verde-800">{{ ucfirst($sugerencia['tipo']) }}:</strong> {{ $sugerencia['mensaje'] }}</li>
                @endforeach
            </ul>
        </section>
    @endif

</div>

@script
<script>
    function ponerEnCampo(id, valor) {
        const el = document.getElementById(id);
        if (el) {
            el.value = valor;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    window.addEventListener('ia-resumen', (evento) => {
        ponerEnCampo('resumen', evento.detail.resumen ?? '');
        ponerEnCampo('puntos_clave', (evento.detail.puntos ?? []).join('\n'));
    });
    window.addEventListener('ia-titulo', (evento) => ponerEnCampo('titulo', evento.detail.titulo ?? ''));
    window.addEventListener('ia-etiquetas', (evento) => ponerEnCampo('etiquetas_ia', (evento.detail.etiquetas ?? []).join(', ')));
</script>
@endscript
