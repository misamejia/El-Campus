<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
                {{ __('Categorías') }}
            </h2>
            <a href="{{ route('categorias.create') }}" class="inline-flex items-center px-4 py-2 bg-verde-800 border border-transparent rounded-md font-semibold text-xs text-papel uppercase tracking-widest hover:bg-verde-700 focus:outline-none focus:ring-2 focus:ring-verde-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                + Crear categoría
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('exito'))
                <x-alerta tipo="exito" class="mb-6">{{ session('exito') }}</x-alerta>
            @endif

            @if (session('error'))
                <x-alerta tipo="error" class="mb-6">{{ session('error') }}</x-alerta>
            @endif

            <div class="bg-white overflow-hidden shadow-sm border border-neutral-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-papel">
                            <tr class="font-sans text-xs uppercase tracking-widest text-neutral-500">
                                <th class="px-4 py-3 text-start">Nombre</th>
                                <th class="px-4 py-3 text-start">Descripción</th>
                                <th class="px-4 py-3 text-start">Estado</th>
                                <th class="px-4 py-3 text-center">Noticias</th>
                                <th class="px-4 py-3 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse ($categorias as $categoriaItem)
                                <tr class="hover:bg-papel transition-colors">
                                    <td class="px-4 py-4 font-serif text-sm font-semibold text-neutral-800">
                                        <a href="{{ route('categorias.show', $categoriaItem) }}" class="hover:text-verde-700">{{ $categoriaItem->nombre }}</a>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600 max-w-xs">
                                        {{ Str::limit($categoriaItem->descripcion, 70) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($categoriaItem->activa)
                                            <span class="inline-block rounded bg-green-100 px-2 py-0.5 font-sans text-xs font-semibold text-green-800">Activo</span>
                                        @else
                                            <span class="inline-block rounded bg-neutral-200 px-2 py-0.5 font-sans text-xs font-semibold text-neutral-700">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600 text-center">
                                        <span class="inline-block rounded bg-verde-50 px-2 py-0.5 font-sans text-xs font-semibold text-verde-800">{{ $categoriaItem->noticias_count }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-3 text-sm">
                                            <a href="{{ route('categorias.show', $categoriaItem) }}" class="font-semibold text-verde-700 hover:text-verde-500">Ver</a>
                                            <a href="{{ route('categorias.edit', $categoriaItem) }}" class="font-semibold text-oro-700 hover:text-oro-500">Editar</a>
                                            <form method="POST" action="{{ route('categorias.destroy', $categoriaItem) }}" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-700 hover:text-red-500">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center font-sans text-sm text-neutral-500">
                                        No hay categorías registradas todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-6 text-sm text-neutral-500">
                Se muestran {{ $categorias->count() }} categoría(s).
            </p>
        </div>
    </div>
</x-app-layout>