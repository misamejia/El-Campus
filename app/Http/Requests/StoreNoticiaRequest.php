<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoticiaRequest extends FormRequest
{
    /**
     * La autorización la realiza el middleware de rol de la ruta.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $reglas = [
            'titulo' => ['required', 'string', 'max:255'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'autor_id' => ['required', 'integer', 'exists:autores,id'],
            'resumen' => ['nullable', 'string', 'max:500'],
            'contenido' => ['required', 'string'],
            'imagen' => ['nullable'],
            'imagen_alt' => [$this->tendraImagen() ? 'required' : 'nullable', 'string', 'max:255'],
            'quitar_imagen' => ['sometimes', 'boolean'],
            'fecha_publicacion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['borrador', 'publicada', 'archivada'])],
            'destacada' => ['sometimes', 'boolean'],
            'puntos_clave' => ['nullable', 'string'],
            'etiquetas_ia' => ['nullable', 'string'],
        ];

        if ($this->hasFile('imagen')) {
            $reglas['imagen'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'];
        } else {
            $reglas['imagen'] = ['nullable', 'string', 'max:2048'];
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'imagen_alt.required' => 'El texto alternativo es obligatorio cuando la noticia tiene una imagen.',
            'imagen_alt.max' => 'El texto alternativo no puede superar los 255 caracteres.',
        ];
    }

    /**
     * En el alta la noticia solo tiene imagen si se sube un archivo nuevo.
     */
    private function tendraImagen(): bool
    {
        return $this->hasFile('imagen');
    }
}
