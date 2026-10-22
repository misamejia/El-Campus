<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Editar evento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-validation-errors class="mb-6" />

            <form method="POST" action="{{ route('eventos.update', $evento) }}" class="bg-white border border-neutral-200 shadow-sm p-8">
                @csrf
                @method('PUT')

                @include('eventos._form', [
                    'evento' => $evento,
                    'boton' => 'Actualizar evento',
                ])
            </form>
        </div>
    </div>
</x-app-layout>