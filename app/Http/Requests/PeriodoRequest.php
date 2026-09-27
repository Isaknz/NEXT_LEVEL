<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $periodo = $this->route('periodo');

        return [
            'codigo' => [
                'required', 'string', 'max:30',
                Rule::unique('periodos_academicos', 'codigo')->ignore($periodo?->id_periodo, 'id_periodo'),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(['PLANIFICADO', 'ABIERTO', 'CERRADO'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'anio' => 'año',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'estado' => 'estado',
        ];
    }

    public function messages(): array
    {
        return ['fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.'];
    }
}
