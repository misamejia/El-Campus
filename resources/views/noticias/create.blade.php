<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
            {{ __('Crear noticia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-validation-errors class="mb-6" />

            <form method="POST" action="{{ route('noticias.store') }}" enctype="multipart/form-data" class="bg-white border border-neutral-200 shadow-sm p-8">
                @csrf

                @include('noticias._form', [
                    'noticia' => null,
                    'categorias' => $categorias,
                    'autores' => $autores,
                    'boton' => 'Guardar noticia',
                ])
            </form>
        </div>
    </div>
</x-app-layout>