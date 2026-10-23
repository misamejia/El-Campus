<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventoRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('eventos', 'slug')->ignore(null)],
            'descripcion' => ['required', 'string'],
            'lugar' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'imagen' => ['nullable'],
            'imagen_alt' => [$this->tendraImagen() ? 'required' : 'nullable', 'string', 'max:255'],
            'quitar_imagen' => ['sometimes', 'boolean'],
            'organizador' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['programado', 'realizado', 'cancelado'])],
        ];

        if ($this->hasFile('imagen')) {
            $reglas['imagen'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'];
        } else {
            $reglas['imagen'] = [
                'bail',
                'nullable',
                'string',
                'max:2048',
                'regex:/^imagenes\/[A-Za-z0-9_\-\/]+\.(jpg|jpeg|png|webp|avif|svg)$/',
                function (string $atributo, mixed $valor, Closure $fallo) {
                    if (is_string($valor) && $valor !== '' && ! is_file(public_path($valor))) {
                        $fallo('La imagen indicada no existe en el servidor.');
                    }
                },
            ];
        }

        return $reglas;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El título del evento es obligatorio.',
            'titulo.max' => 'El título no puede superar los 255 caracteres.',
            'slug.unique' => 'Ya existe un evento con ese slug.',
            'descripcion.required' => 'La descripción del evento es obligatoria.',
            'lugar.required' => 'El lugar del evento es obligatorio.',
            'lugar.max' => 'El lugar no puede superar los 255 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'imagen.max' => 'La ruta de la imagen no puede superar los 2048 caracteres.',
            'imagen_alt.required' => 'El texto alternativo es obligatorio cuando el evento tiene una imagen.',
            'imagen_alt.max' => 'El texto alternativo no puede superar los 255 caracteres.',
            'organizador.max' => 'El organizador no puede superar los 255 caracteres.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }

    /**
     * En el alta el evento tiene imagen si se sube un archivo o si se indica
     * la ruta de una imagen ya publicada.
     */
    private function tendraImagen(): bool
    {
        return $this->hasFile('imagen') || $this->filled('imagen');
    }
}
