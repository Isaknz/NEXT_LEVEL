<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacultadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $facultad = $this->route('facultad');

        return [
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('facultades', 'nombre')->ignore($facultad?->id_facultad, 'id_facultad'),
            ],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function attributes(): array
    {
        return ['nombre' => 'nombre', 'estado' => 'estado'];
    }
}
