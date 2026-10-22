<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Editar autor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-validation-errors class="mb-6" />

            <form method="POST" action="{{ route('autores.update', $autor) }}" class="bg-white border border-neutral-200 shadow-sm p-8">
                @csrf
                @method('PUT')

                @include('autores._form', [
                    'autor' => $autor,
                    'boton' => 'Actualizar autor',
                ])
            </form>
        </div>
    </div>
</x-app-layout>