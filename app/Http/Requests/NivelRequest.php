<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $nivel = $this->route('nivel');

        return [
            'codigo' => [
                'required', 'string', 'max:20',
                Rule::unique('niveles', 'codigo')->ignore($nivel?->id_nivel, 'id_nivel'),
            ],
            'nombre' => [
                'required', 'string', 'max:60',
                Rule::unique('niveles', 'nombre')->ignore($nivel?->id_nivel, 'id_nivel'),
            ],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'estado' => 'estado',
        ];
    }
}
