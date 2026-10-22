@php $campo = fn ($campo) => old($campo, $evento?->$campo); @endphp

<div class="grid gap-6">
    <div>
        <x-label for="titulo" value="{{ __('Título del evento') }}" />
        <x-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="$campo('titulo')" required autofocus />
    </div>

    <div>
        <x-label for="lugar" value="{{ __('Lugar') }}" />
        <x-input id="lugar" name="lugar" type="text" class="mt-1 block w-full" :value="$campo('lugar')" required />
    </div>

    <div>
        <x-label for="fecha_inicio" value="{{ __('Fecha de inicio') }}" />
        <x-input id="fecha_inicio" name="fecha_inicio" type="datetime-local" class="mt-1 block w-full" :value="old('fecha_inicio', $evento?->fecha_inicio?->format('Y-m-d\TH:i'))" required />
    </div>

    <div>
        <x-label for="fecha_fin" value="{{ __('Fecha de fin') }}" />
        <x-input id="fecha_fin" name="fecha_fin" type="datetime-local" class="mt-1 block w-full" :value="old('fecha_fin', $evento?->fecha_fin?->format('Y-m-d\TH:i'))" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Debe ser igual o posterior a la fecha de inicio.</p>
    </div>

    <div>
        <x-label for="descripcion" value="{{ __('Descripción') }}" />
        <textarea id="descripcion" name="descripcion" rows="8" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ $campo('descripcion') }}</textarea>
    </div>

    <div>
        <x-label for="organizador" value="{{ __('Organizador') }}" />
        <x-input id="organizador" name="organizador" type="text" class="mt-1 block w-full" :value="old('organizador', $evento?->organizador?->nombre)" placeholder="Bienestar Universitario, Facultad, Área Deportiva…" />
        <p class="mt-1 text-xs text-neutral-500">Opcional.</p>
    </div>

    <div>
        <x-label for="imagen" value="{{ __('Imagen (ruta)') }}" />
        <x-input id="imagen" name="imagen" type="text" class="mt-1 block w-full" :value="$campo('imagen')" placeholder="imagenes/demo-cultura.svg" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Ruta relativa a <code>public/</code>.</p>
    </div>

    <div>
        <x-label for="imagen_alt" value="{{ __('Texto alternativo de la imagen') }}" />
        <x-input id="imagen_alt" name="imagen_alt" type="text" class="mt-1 block w-full" maxlength="255"
            aria-describedby="imagen_alt_ayuda" :value="old('imagen_alt', $evento?->medio?->texto_alternativo)" />
        <p id="imagen_alt_ayuda" class="mt-1 text-xs text-neutral-500">Describe la imagen para quienes usan lectores de pantalla. Si lo dejas vacío, se usará el título del evento.</p>
        <p class="mt-1 text-xs text-neutral-500">Este texto lo comparten todos los eventos que usen la misma imagen.</p>
        <x-input-error for="imagen_alt" class="mt-1" />
    </div>

    <div>
        <x-label for="estado" value="{{ __('Estado') }}" />
        <select id="estado" name="estado" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="programado" @selected(old('estado', $evento?->estado?->codigo ?? 'programado') === 'programado')>Programado</option>
            <option value="realizado" @selected(old('estado', $evento?->estado?->codigo ?? 'programado') === 'realizado')>Realizado</option>
            <option value="cancelado" @selected(old('estado', $evento?->estado?->codigo ?? 'programado') === 'cancelado')>Cancelado</option>
        </select>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button>{{ $boton }}</x-button>
        <x-boton variante="secundario" href="{{ route('eventos.index') }}">Cancelar</x-boton>
    </div>
</div>