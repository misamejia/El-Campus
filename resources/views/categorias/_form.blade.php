@php $campo = fn ($campo) => old($campo, $categoria?->$campo); @endphp

<div class="grid gap-6">
    <div>
        <x-label for="nombre" value="{{ __('Nombre') }}" />
        <x-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="$campo('nombre')" required autofocus />
    </div>

    <div>
        <x-label for="slug" value="{{ __('Slug') }}" />
        <x-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="$campo('slug')" placeholder="universidad" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Si se deja vacío, se genera automáticamente a partir del nombre.</p>
    </div>

    <div>
        <x-label for="descripcion" value="{{ __('Descripción') }}" />
        <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $campo('descripcion') }}</textarea>
        <p class="mt-1 text-xs text-neutral-500">Opcional. Breve descripción de la sección editorial.</p>
    </div>

    <div>
        <x-label for="orden" value="{{ __('Orden') }}" />
        <x-input id="orden" name="orden" type="number" min="0" class="mt-1 block w-full" :value="$campo('orden') ?? 0" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Número para ordenar las secciones en la portada y el menú.</p>
    </div>

    <div>
        <label for="activa" class="flex items-center">
            <input type="checkbox" id="activa" name="activa" value="1" {{ old('activa', $categoria?->activa ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-verde-600 shadow-sm focus:ring-verde-500">
            <span class="ms-2 text-sm text-neutral-700">Categoría activa (visible para nuevas publicaciones)</span>
        </label>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button>{{ $boton }}</x-button>
        <x-boton variante="secundario" href="{{ route('categorias.index') }}">Cancelar</x-boton>
    </div>
</div>
