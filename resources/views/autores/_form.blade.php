@php $campo = fn ($campo) => old($campo, $autor?->$campo); @endphp

<div class="grid gap-6">
    <div>
        <x-label for="nombre" value="{{ __('Nombre') }}" />
        <x-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="$campo('nombre')" required autofocus />
    </div>

    <div>
        <x-label for="slug" value="{{ __('Slug') }}" />
        <x-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="$campo('slug')" placeholder="redaccion-el-campus" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Si se deja vacío, se genera automáticamente a partir del nombre.</p>
    </div>

    <div>
        <x-label for="descripcion" value="{{ __('Descripción') }}" />
        <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $campo('descripcion') }}</textarea>
        <p class="mt-1 text-xs text-neutral-500">Opcional. Breve descripción del autor o equipo editorial.</p>
    </div>

    <div>
        <x-label for="email" value="{{ __('Email') }}" />
        <x-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$campo('email')" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Correo institucional de contacto.</p>
    </div>

    <div>
        <label for="activo" class="flex items-center">
            <input type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $autor?->activo ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-verde-600 shadow-sm focus:ring-verde-500">
            <span class="ms-2 text-sm text-neutral-700">Autor activo (puede publicar nuevas noticias)</span>
        </label>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button>{{ $boton }}</x-button>
        <x-boton variante="secundario" href="{{ route('autores.index') }}">Cancelar</x-boton>
    </div>
</div>