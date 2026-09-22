<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $matricula = $this->route('matricula');

        return [
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('matriculas', 'codigo')->ignore($matricula?->id_matricula, 'id_matricula'),
            ],
            'id_alumno' => 'required|exists:alumnos,id_alumno',
            'id_periodo' => 'required|exists:periodos_academicos,id_periodo',
            'id_nivel' => 'required|exists:niveles,id_nivel',
            'modalidad' => 'required|in:ESCOLAR,ACADEMIA',
            'id_grado' => 'required_if:modalidad,ESCOLAR|nullable|exists:grados,id_grado',
            'id_ciclo' => 'required_if:modalidad,ACADEMIA|nullable|exists:ciclos_academia,id_ciclo',
            'fecha_matricula' => 'required|date',
            'tipo_matricula' => 'required|in:NUEVO,REGULAR,TRASLADO,REINGRESO',
            'estado' => 'required|in:PENDIENTE,ACTIVA,RETIRADA,ANULADA,FINALIZADA',
            'observaciones' => 'nullable|string|max:500',
        ];
    }
}