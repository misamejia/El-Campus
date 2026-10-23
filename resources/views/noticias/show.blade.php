<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Noticia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <article class="bg-white border border-neutral-200 shadow-sm p-8">
                <div class="flex flex-wrap gap-2 font-sans text-xs uppercase tracking-widest text-neutral-500">
                    <span class="rounded bg-verde-50 px-2 py-1 font-semibold text-verde-800">{{ $noticia->categoria?->nombre }}</span>
                    <span class="rounded bg-neutral-100 px-2 py-1">{{ $noticia->estado?->nombre }}</span>
                    @if ($noticia->destacada)
                        <span class="rounded bg-oro-100 px-2 py-1 font-semibold text-oro-900">★ Destacada</span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-black text-verde-950 leading-tight">
                    {{ $noticia->titulo }}
                </h1>

                <p class="mt-3 text-sm text-neutral-500">
                    Por <strong class="text-neutral-700">{{ $noticia->autor?->nombre }}</strong>
                    · {{ $noticia->fecha_publicacion?->format('d \d\e F \d\e Y, H:i') ?: 'Sin fecha de publicación' }}
                </p>

                @if ($noticia->imagen)
                    <div class="mt-6">
                        <img src="{{ asset($noticia->imagen) }}" alt="{{ $noticia->imagen_alt ?: $noticia->titulo }}" class="w-full object-cover">
                    </div>
                @endif

                @if ($noticia->resumen)
                    <p class="mt-6 font-serif text-lg italic leading-relaxed text-neutral-700">
                        {{ $noticia->resumen }}
                    </p>
                @endif

                <div class="mt-6 space-y-4 font-serif text-base leading-relaxed text-neutral-800">
                    {!! nl2br(e($noticia->contenido)) !!}
                </div>

                @if ($noticia->historialEstados->isNotEmpty())
                    <section class="mt-10 border-t border-neutral-200 pt-6">
                        <h2 class="font-display text-lg font-bold text-verde-950">Historial de estados</h2>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 text-sm">
                                <thead class="bg-neutral-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-neutral-600">Estado anterior</th>
                                        <th class="px-3 py-2 text-left font-semibold text-neutral-600">Estado nuevo</th>
                                        <th class="px-3 py-2 text-left font-semibold text-neutral-600">Usuario</th>
                                        <th class="px-3 py-2 text-left font-semibold text-neutral-600">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 bg-white">
                                    @foreach ($noticia->historialEstados as $historial)
                                        <tr>
                                            <td class="px-3 py-2 text-neutral-700">{{ $historial->estadoAnterior?->nombre ?? '—' }}</td>
                                            <td class="px-3 py-2 text-neutral-700">{{ $historial->estadoNuevo?->nombre ?? '—' }}</td>
                                            <td class="px-3 py-2 text-neutral-700">{{ $historial->usuario?->name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-neutral-700">{{ $historial->cambiado_en?->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </article>

            <div class="mt-6 flex items-center gap-3">
                <x-boton href="{{ route('noticias.index') }}">Volver al listado</x-boton>
                <x-boton variante="secundario" href="{{ route('noticias.edit', $noticia) }}">Editar noticia</x-boton>
            </div>
        </div>
    </div>
</x-app-layout>