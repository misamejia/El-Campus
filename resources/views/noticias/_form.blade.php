@php $campo = fn ($campo) => old($campo, $noticia?->$campo); @endphp

<div class="grid gap-6">
    <div>
        <x-label for="titulo" value="{{ __('Título') }}" />
        <x-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="$campo('titulo')" required autofocus />
    </div>

    <div>
        <x-label for="categoria_id" value="{{ __('Categoría') }}" />
        <select id="categoria_id" name="categoria_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Selecciona una categoría —</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_id', $noticia?->categoria_id) == $categoria->id)>
                    {{ $categoria->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <x-label for="resumen" value="{{ __('Resumen (entradilla)') }}" />
        <textarea id="resumen" name="resumen" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $campo('resumen') }}</textarea>
        <p class="mt-1 text-xs text-neutral-500">Opcional. Máximo 500 caracteres. El asistente puede generarlo por ti.</p>
    </div>

    <div>
        <x-label for="puntos_clave" value="{{ __('Puntos clave') }}" />
        <textarea id="puntos_clave" name="puntos_clave" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('puntos_clave', is_array($noticia?->puntos_clave) ? implode("\n", $noticia->puntos_clave) : '') }}</textarea>
        <p class="mt-1 text-xs text-neutral-500">Opcional. Un punto clave por línea.</p>
    </div>

    <div>
        <x-label for="contenido" value="{{ __('Contenido') }}" />
        <textarea id="contenido" name="contenido" rows="12" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ $campo('contenido') }}</textarea>
    </div>

    <div>
        <x-label for="etiquetas_ia" value="{{ __('Etiquetas') }}" />
        <x-input id="etiquetas_ia" name="etiquetas_ia" type="text" class="mt-1 block w-full" :value="old('etiquetas_ia', is_array($noticia?->etiquetas_ia) ? implode(', ', $noticia->etiquetas_ia) : '')" />
        <p class="mt-1 text-xs text-neutral-500">Opcional. Palabras clave separadas por comas.</p>
    </div>

    <livewire:noticias.asistente-ia />

    <div>
        <x-label for="imagen" value="{{ __('Imagen') }}" />
        <input id="imagen" name="imagen" type="file" accept=".jpg,.jpeg,.png,.webp,.avif"
            class="mt-1 block w-full text-sm text-neutral-700 file:me-3 file:rounded file:border-0 file:bg-verde-800 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-papel hover:file:bg-verde-700" />
        @if ($noticia?->imagen)
            <div class="mt-3 flex flex-wrap items-center gap-4">
                <img src="{{ asset($noticia->imagen) }}" alt="Imagen actual de la noticia"
                    class="h-24 w-40 rounded border border-neutral-200 object-cover">
                <label class="flex items-center text-sm text-neutral-700">
                    <input type="checkbox" id="quitar_imagen" name="quitar_imagen" value="1"
                        {{ old('quitar_imagen') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-verde-600 shadow-sm focus:ring-verde-500">
                    <span class="ms-2">Quitar imagen actual</span>
                </label>
            </div>
        @endif
        <p class="mt-1 text-xs text-neutral-500">Opcional. JPG, PNG, WebP o AVIF, máximo 2 MB.</p>
    </div>

    <div>
        <x-label for="imagen_alt" value="{{ __('Texto alternativo de la imagen') }}" />
        <x-input id="imagen_alt" name="imagen_alt" type="text" class="mt-1 block w-full" maxlength="255"
            aria-describedby="imagen_alt_ayuda" :value="old('imagen_alt', $noticia?->medio?->texto_alternativo)" />
        <p id="imagen_alt_ayuda" class="mt-1 text-xs text-neutral-500">Describe la imagen para quienes usan lectores de pantalla. Si lo dejas vacío, se usará el título de la noticia.</p>
        <x-input-error for="imagen_alt" class="mt-1" />
    </div>

    <div>
        <x-label for="autor_id" value="{{ __('Autor') }}" />
        <select id="autor_id" name="autor_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Selecciona un autor —</option>
            @foreach ($autores as $autor)
                <option value="{{ $autor->id }}" @selected(old('autor_id', $noticia?->autor_id) == $autor->id)>
                    {{ $autor->nombre }}@if (! $autor->activo) (inactivo)@endif
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <x-label for="fecha_publicacion" value="{{ __('Fecha de publicación') }}" />
        <x-input id="fecha_publicacion" name="fecha_publicacion" type="datetime-local" class="mt-1 block w-full" :value="old('fecha_publicacion', $noticia?->fecha_publicacion?->format('Y-m-d\TH:i'))" />
    </div>

    <div>
        <x-label for="estado" value="{{ __('Estado') }}" />
        <select id="estado" name="estado" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="borrador" @selected(old('estado', $noticia?->estado?->codigo ?? 'borrador') === 'borrador')>Borrador</option>
            <option value="publicada" @selected(old('estado', $noticia?->estado?->codigo ?? 'borrador') === 'publicada')>Publicada</option>
            <option value="archivada" @selected(old('estado', $noticia?->estado?->codigo ?? 'borrador') === 'archivada')>Archivada</option>
        </select>
    </div>

    <div>
        <label for="destacada" class="flex items-center">
            <input type="checkbox" id="destacada" name="destacada" value="1" {{ old('destacada', $noticia?->destacada ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-verde-600 shadow-sm focus:ring-verde-500">
            <span class="ms-2 text-sm text-neutral-700">Marcar como noticia destacada</span>
        </label>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-button>{{ $boton }}</x-button>
        <x-boton variante="secundario" href="{{ route('noticias.index') }}">Cancelar</x-boton>
    </div>
</div>