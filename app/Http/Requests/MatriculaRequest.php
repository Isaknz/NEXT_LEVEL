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

            // Las FK son compuestas: grados(id_grado, id_nivel) y
            // ciclos_academia(id_ciclo, id_periodo). Validar solo el id suelto
            // dejaba pasar combinaciones imposibles que el MySQL de desarrollo
            // rechazaba con un 500 en lugar de un error de formulario.
            'id_grado' => [
                'required_if:modalidad,ESCOLAR',
                'nullable',
                Rule::exists('grados', 'id_grado')->where(
                    fn ($q) => $q->where('id_nivel', $this->input('id_nivel'))
                ),
            ],
            'id_ciclo' => [
                'required_if:modalidad,ACADEMIA',
                'nullable',
                Rule::exists('ciclos_academia', 'id_ciclo')->where(
                    fn ($q) => $q->where('id_periodo', $this->input('id_periodo'))
                ),
            ],
            'fecha_matricula' => 'required|date',
            'tipo_matricula' => 'required|in:NUEVO,REGULAR,TRASLADO,REINGRESO',
            'estado' => 'required|in:PENDIENTE,ACTIVA,RETIRADA,ANULADA,FINALIZADA,PAGADA',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_grado' => 'grado',
            'id_ciclo' => 'ciclo',
            'id_periodo' => 'periodo académico',
            'id_nivel' => 'nivel',
        ];
    }

    public function messages(): array
    {
        return [
            'id_grado.exists' => 'El grado seleccionado no pertenece al nivel elegido.',
            'id_ciclo.exists' => 'El ciclo seleccionado no pertenece al periodo académico elegido.',
        ];
    }
}