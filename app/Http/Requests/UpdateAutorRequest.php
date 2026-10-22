<?php

namespace App\Http\Requests;

use App\Models\Autor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAutorRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('autores', 'slug')->ignore($this->autor()->id)],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Autor resuelto por la ruta.
     */
    private function autor(): Autor
    {
        return $this->route('autor');
    }
}
