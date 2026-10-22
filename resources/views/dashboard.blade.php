<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Panel administrativo') }}
        </h2>
        <p class="mt-1 font-sans text-sm text-neutral-500">
            {{ __('Gestiona el contenido de El Campus') }}
        </p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="font-sans text-xs uppercase tracking-[0.3em] text-oro-700 font-semibold">
                {{ __('Zona protegida') }}
            </p>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <h3 class="font-display text-2xl font-black text-verde-800">
                    {{ __('Hola,') }} {{ Auth::user()->name }}
                </h3>
                @if (Auth::user()->isAdministrador())
                    <span class="inline-block rounded bg-oro-100 px-3 py-1 font-sans text-xs font-semibold uppercase tracking-wide text-oro-900">
                        Rol: Administrador
                    </span>
                @else
                    <span class="inline-block rounded bg-verde-50 px-3 py-1 font-sans text-xs font-semibold uppercase tracking-wide text-verde-800">
                        Rol: Editor
                    </span>
                @endif
            </div>
            <p class="mt-1 max-w-3xl font-sans text-sm leading-relaxed text-neutral-600">
                {{ __('Desde aquí puedes revisar el estado del periódico y acceder a la gestión de noticias, autores y eventos.') }}
            </p>

            {{-- Resumen general --}}
            <section aria-label="Resumen general" class="mt-10">
                <h4 class="font-display text-lg font-bold text-neutral-900">Resumen general</h4>
                <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <x-tarjeta-estadistica titulo="Noticias" :valor="$totalNoticias" descripcion="Contenido editorial del periódico" enlace="{{ route('noticias.index') }}" />
                    <x-tarjeta-estadistica titulo="Autores" :valor="$totalAutores" descripcion="Firmas y equipos editoriales" :enlace="Auth::user()->isAdministrador() ? route('autores.index') : null" />
                    <x-tarjeta-estadistica titulo="Categorías" :valor="$totalCategorias" descripcion="Secciones del periódico" />
                    <x-tarjeta-estadistica titulo="Eventos" :valor="$totalEventos" descripcion="Agenda universitaria" enlace="{{ route('eventos.index') }}" />
                </div>
            </section>

            {{-- Estado del contenido --}}
            <section aria-label="Estado del contenido" class="mt-12">
                <div class="grid gap-8 lg:grid-cols-2">
                    <div class="rounded-lg border border-neutral-200 bg-marfil p-6 shadow-sm">
                        <h4 class="flex items-center justify-between font-display text-lg font-bold text-neutral-900">
                            Noticias
                            <span class="font-sans text-sm font-semibold text-neutral-500">{{ $totalNoticias }} en total</span>
                        </h4>
                        <ul class="mt-4 space-y-2 font-sans text-sm text-neutral-700">
                            <li class="flex items-center justify-between rounded bg-verde-50 px-3 py-2">
                                <span class="font-medium text-verde-900">Publicadas</span>
                                <strong class="text-verde-900">{{ $publicadas }}</strong>
                                <span class="sr-only">Publicadas: {{ $publicadas }}</span>
                            </li>
                            <li class="flex items-center justify-between rounded bg-neutral-100 px-3 py-2">
                                <span class="font-medium text-neutral-700">En borrador</span>
                                <strong class="text-neutral-800">{{ $borradores }}</strong>
                                <span class="sr-only">En borrador: {{ $borradores }}</span>
                            </li>
                            <li class="flex items-center justify-between rounded bg-oro-50 px-3 py-2">
                                <span class="font-medium text-oro-900">Archivadas</span>
                                <strong class="text-oro-900">{{ $archivadas }}</strong>
                                <span class="sr-only">Archivadas: {{ $archivadas }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-neutral-200 bg-marfil p-6 shadow-sm">
                        <h4 class="flex items-center justify-between font-display text-lg font-bold text-neutral-900">
                            Eventos
                            <span class="font-sans text-sm font-semibold text-neutral-500">{{ $totalEventos }} en total</span>
                        </h4>
                        <ul class="mt-4 space-y-2 font-sans text-sm text-neutral-700">
                            <li class="flex items-center justify-between rounded bg-verde-50 px-3 py-2">
                                <span class="font-medium text-verde-900">Programados</span>
                                <strong class="text-verde-900">{{ $programados }}</strong>
                                <span class="sr-only">Programados: {{ $programados }}</span>
                            </li>
                            <li class="flex items-center justify-between rounded bg-oro-50 px-3 py-2">
                                <span class="font-medium text-oro-900">Realizados</span>
                                <strong class="text-oro-900">{{ $realizados }}</strong>
                                <span class="sr-only">Realizados: {{ $realizados }}</span>
                            </li>
                            <li class="flex items-center justify-between rounded bg-red-50 px-3 py-2">
                                <span class="font-medium text-red-900">Cancelados</span>
                                <strong class="text-red-900">{{ $cancelados }}</strong>
                                <span class="sr-only">Cancelados: {{ $cancelados }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Actividad reciente --}}
            <section aria-label="Actividad reciente" class="mt-12">
                <h4 class="font-display text-lg font-bold text-neutral-900">Actividad reciente</h4>
                <div class="mt-4 grid gap-8 lg:grid-cols-2">
                    <div class="rounded-lg border border-neutral-200 bg-marfil p-6 shadow-sm">
                        <h5 class="font-sans text-xs font-semibold uppercase tracking-widest text-neutral-500">Noticias recientes</h5>
                        @php
                            $estadoEstilos = [
                                'publicada' => 'bg-verde-100 text-verde-800',
                                'borrador' => 'bg-neutral-200 text-neutral-700',
                                'archivada' => 'bg-oro-100 text-oro-900',
                            ];
                        @endphp
                        <ul class="mt-4 divide-y divide-neutral-100">
                            @forelse ($noticiasRecientes as $noticia)
                                <li class="py-3">
                                    <a href="{{ route('noticias.show', $noticia) }}" class="font-serif text-sm font-semibold text-neutral-800 hover:text-verde-700">
                                        {{ $noticia->titulo }}
                                    </a>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 font-sans text-xs text-neutral-500">
                                        <span>{{ $noticia->autor?->nombre ?: 'Sin autor' }}</span>
                                        <span>{{ $noticia->fecha_publicacion?->format('d/m/Y') ?: 'Sin fecha' }}</span>
                                        <span class="rounded px-1.5 py-0.5 font-semibold {{ $estadoEstilos[$noticia->estado?->codigo] ?? 'bg-neutral-200 text-neutral-700' }}">
                                            {{ $noticia->estado?->nombre }}
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="py-6 text-center font-sans text-sm text-neutral-500">No hay noticias registradas todavía.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="rounded-lg border border-neutral-200 bg-marfil p-6 shadow-sm">
                        <h5 class="font-sans text-xs font-semibold uppercase tracking-widest text-neutral-500">Próximos eventos</h5>
                        <ul class="mt-4 divide-y divide-neutral-100">
                            @forelse ($proximosEventos as $evento)
                                <li class="py-3">
                                    <div class="flex flex-wrap items-center gap-x-3 font-sans text-xs text-neutral-500">
                                        <span class="rounded bg-verde-50 px-1.5 py-0.5 font-semibold text-verde-800">
                                            {{ $evento->fecha_inicio?->isoFormat('D [de] MMMM') }}
                                        </span>
                                        <span>{{ $evento->lugar }}</span>
                                    </div>
                                    <a href="{{ route('eventos.show', $evento) }}" class="mt-1 block font-serif text-sm font-semibold text-neutral-800 hover:text-verde-700">
                                        {{ $evento->titulo }}
                                    </a>
                                </li>
                            @empty
                                <li class="py-6 text-center font-sans text-sm text-neutral-500">No hay próximos eventos programados.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </section>

            {{-- Accesos rápidos --}}
            <section aria-label="Accesos rápidos" class="mt-12">
                <h4 class="font-display text-lg font-bold text-neutral-900">Accesos rápidos</h4>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-boton href="{{ route('noticias.index') }}">Gestionar noticias</x-boton>
                    @if (Auth::user()->isAdministrador())
                        <x-boton variante="secundario" href="{{ route('autores.index') }}">Gestionar autores</x-boton>
                    @endif
                    <x-boton variante="secundario" href="{{ route('eventos.index') }}">Gestionar eventos</x-boton>
                    @if (Auth::user()->isAdministrador())
                        <x-boton variante="secundario" href="{{ route('usuarios.index') }}">Gestionar usuarios</x-boton>
                    @endif
                    <x-boton variante="fantasma" href="{{ route('inicio') }}">Ver portada</x-boton>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>