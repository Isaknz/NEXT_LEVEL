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
            'id_alumno' => 'nullable|required_without:alumno_nombres|exists:alumnos,id_alumno',
            'alumno_nombres' => 'nullable|required_without:id_alumno|string|max:100',
            'alumno_apellidos' => 'nullable|required_with:alumno_nombres|string|max:100',
            'alumno_dni' => [
                'nullable',
                'string',
                'size:8',
                Rule::unique('alumnos', 'dni')->whereNull('deleted_at'),
            ],
            'alumno_fecha_nacimiento' => 'nullable|date',
            'alumno_sexo' => 'nullable|in:F,M,OTRO,NO_DECLARA',
            'alumno_celular' => 'nullable|string|max:20',
            'alumno_email' => 'nullable|email|max:120',
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
            'id_alumno' => 'alumno',
            'alumno_nombres' => 'nombres del alumno',
            'alumno_apellidos' => 'apellidos del alumno',
            'alumno_dni' => 'DNI del alumno',
            'id_grado' => 'grado',
            'id_ciclo' => 'ciclo',
            'id_periodo' => 'periodo académico',
            'id_nivel' => 'nivel',
        ];
    }

    public function messages(): array
    {
        return [
            'id_alumno.required_without' => 'Selecciona un alumno existente o registra uno nuevo.',
            'alumno_nombres.required_without' => 'Selecciona un alumno existente o registra uno nuevo.',
            'alumno_dni.unique' => 'Ya existe un alumno con ese DNI.',
            'id_grado.exists' => 'El grado seleccionado no pertenece al nivel elegido.',
            'id_ciclo.exists' => 'El ciclo seleccionado no pertenece al periodo académico elegido.',
        ];
    }
}