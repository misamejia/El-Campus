<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
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
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categorias', 'slug')->ignore($this->route('categoria')->id)],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activa' => ['sometimes', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
