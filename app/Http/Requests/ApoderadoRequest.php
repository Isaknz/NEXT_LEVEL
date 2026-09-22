<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApoderadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $apoderado = $this->route('apoderado');

        return [
            'dni' => [
                'nullable',
                'string',
                'size:8',
                Rule::unique('apoderados', 'dni')
                    ->whereNull('deleted_at')
                    ->ignore($apoderado?->id_apoderado, 'id_apoderado'),
            ],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ];
    }
}