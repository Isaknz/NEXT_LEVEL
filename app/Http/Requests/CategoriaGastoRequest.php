<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaGastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $categoria = $this->route('categoria');

        return [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('categorias_gasto', 'nombre')->ignore($categoria?->id_categoria_gasto, 'id_categoria_gasto'),
            ],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function attributes(): array
    {
        return ['nombre' => 'nombre', 'estado' => 'estado'];
    }
}
