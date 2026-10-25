<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
                {{ __('Noticias') }}
            </h2>
            <a href="{{ route('noticias.create') }}" class="inline-flex items-center px-4 py-2 bg-verde-800 border border-transparent rounded-md font-semibold text-xs text-papel uppercase tracking-widest hover:bg-verde-700 focus:outline-none focus:ring-2 focus:ring-verde-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                + Crear noticia
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('exito'))
                <x-alerta tipo="exito" class="mb-6">{{ session('exito') }}</x-alerta>
            @endif

            <div class="bg-white overflow-hidden shadow-sm border border-neutral-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-papel">
                            <tr class="font-sans text-xs uppercase tracking-widest text-neutral-600">
                                <th class="px-4 py-3 text-start">Título</th>
                                <th class="px-4 py-3 text-start">Categoría</th>
                                <th class="px-4 py-3 text-start">Autor</th>
                                <th class="px-4 py-3 text-start">Estado</th>
                                <th class="px-4 py-3 text-start">Fecha</th>
                                <th class="px-4 py-3 text-start">Destacada</th>
                                <th class="px-4 py-3 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse ($noticias as $noticia)
                                <tr class="hover:bg-papel transition-colors">
                                    <td class="px-4 py-4 font-serif text-sm font-semibold text-neutral-800 max-w-xs">
                                        <a href="{{ route('noticias.show', $noticia) }}" class="hover:text-verde-700">{{ $noticia->titulo }}</a>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600">
                                        <span class="inline-block rounded bg-verde-50 px-2 py-0.5 font-sans text-xs font-semibold text-verde-800">{{ $noticia->categoria?->nombre }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600">{{ $noticia->autor?->nombre }}</td>
                                    <td class="px-4 py-4">
                                        @php
                                            $estadoEstilos = [
                                                'publicada' => 'bg-green-100 text-green-800',
                                                'borrador' => 'bg-neutral-200 text-neutral-700',
                                                'archivada' => 'bg-oro-100 text-oro-900',
                                            ];
                                        @endphp
                                        <span class="inline-block rounded px-2 py-0.5 font-sans text-xs font-semibold capitalize {{ $estadoEstilos[$noticia->estado?->codigo] ?? 'bg-neutral-200 text-neutral-700' }}">{{ $noticia->estado?->nombre }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600">{{ $noticia->fecha_publicacion?->format('d/m/Y H:i') ?: '—' }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $noticia->destacada ? '★' : '' }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-3 text-sm">
                                            <a href="{{ route('noticias.show', $noticia) }}" class="font-semibold text-verde-700 hover:text-verde-500">Ver</a>
                                            <a href="{{ route('noticias.edit', $noticia) }}" class="font-semibold text-oro-900 hover:text-oro-700">
                                            <form method="POST" action="{{ route('noticias.destroy', $noticia) }}" onsubmit="return confirm('¿Eliminar esta noticia? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-700 hover:text-red-500">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center font-sans text-sm text-neutral-600">
                                        No hay noticias registradas todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

                <p class="mt-6 text-sm text-neutral-600">
                Se muestran {{ $noticias->count() }} noticia(s).
            </p>

            <div class="mt-4">
                {{ $noticias->links() }}
            </div>
        </div>
    </div>
</x-app-layout>