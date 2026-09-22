<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $alumno = $this->route('alumno');

        return [
            'codigo' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('alumnos', 'codigo')
                    ->whereNull('deleted_at')
                    ->ignore($alumno?->id_alumno, 'id_alumno'),
            ],
            'dni' => [
                'nullable',
                'string',
                'size:8',
                Rule::unique('alumnos', 'dni')
                    ->whereNull('deleted_at')
                    ->ignore($alumno?->id_alumno, 'id_alumno'),
            ],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,OTRO,NO_DECLARA',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'direccion' => 'nullable|string|max:255',
            'id_grado' => 'nullable|exists:grados,id_grado',
            'id_apoderado' => 'nullable|exists:apoderados,id_apoderado',
            'parentesco' => 'nullable|string|max:20',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ];
    }
}