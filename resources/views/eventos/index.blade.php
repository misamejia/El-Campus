<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
                {{ __('Eventos') }}
            </h2>
            <a href="{{ route('eventos.create') }}" class="inline-flex items-center px-4 py-2 bg-verde-800 border border-transparent rounded-md font-semibold text-xs text-papel uppercase tracking-widest hover:bg-verde-700 focus:outline-none focus:ring-2 focus:ring-verde-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                + Crear evento
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
                            <tr class="font-sans text-xs uppercase tracking-widest text-neutral-500">
                                <th class="px-4 py-3 text-start">Título</th>
                                <th class="px-4 py-3 text-start">Lugar</th>
                                <th class="px-4 py-3 text-start">Fecha de inicio</th>
                                <th class="px-4 py-3 text-start">Estado</th>
                                <th class="px-4 py-3 text-start">Organizador</th>
                                <th class="px-4 py-3 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse ($eventos as $evento)
                                <tr class="hover:bg-papel transition-colors">
                                    <td class="px-4 py-4 font-serif text-sm font-semibold text-neutral-800 max-w-xs">
                                        <a href="{{ route('eventos.show', $evento) }}" class="hover:text-verde-700">{{ $evento->titulo }}</a>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600">{{ $evento->lugar }}</td>
                                    <td class="px-4 py-4 text-sm text-neutral-600">{{ $evento->fecha_inicio?->format('d/m/Y H:i') ?: '—' }}</td>
                                    <td class="px-4 py-4">
                                        @php
                                            $estadoEstilos = [
                                                'programado' => 'bg-verde-100 text-verde-800',
                                                'realizado' => 'bg-oro-100 text-oro-900',
                                                'cancelado' => 'bg-red-100 text-red-800',
                                            ];
                                            $estadoEtiquetas = [
                                                'programado' => 'Programado',
                                                'realizado' => 'Realizado',
                                                'cancelado' => 'Cancelado',
                                            ];
                                        @endphp
                                        <span class="inline-block rounded px-2 py-0.5 font-sans text-xs font-semibold {{ $estadoEstilos[$evento->estado?->codigo] ?? 'bg-neutral-200 text-neutral-700' }}">{{ $estadoEtiquetas[$evento->estado?->codigo] ?? ucfirst($evento->estado?->codigo ?? '') }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600">{{ $evento->organizador?->nombre ?: '—' }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-3 text-sm">
                                            <a href="{{ route('eventos.show', $evento) }}" class="font-semibold text-verde-700 hover:text-verde-500">Ver</a>
                                            <a href="{{ route('eventos.edit', $evento) }}" class="font-semibold text-oro-700 hover:text-oro-500">Editar</a>
                                            <form method="POST" action="{{ route('eventos.destroy', $evento) }}" onsubmit="return confirm('¿Eliminar este evento? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-semibold text-red-700 hover:text-red-500">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center font-sans text-sm text-neutral-500">
                                        No hay eventos registrados todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-6 text-sm text-neutral-500">
                Se muestran {{ $eventos->count() }} evento(s).
            </p>
        </div>
    </div>
</x-app-layout>