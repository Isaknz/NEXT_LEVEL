<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConceptoCobroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $concepto = $this->route('concepto');

        return [
            'codigo' => [
                'required', 'string', 'max:40',
                Rule::unique('conceptos_cobro', 'codigo')->ignore($concepto?->id_concepto, 'id_concepto'),
            ],
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', Rule::in(['MATRICULA', 'MENSUALIDAD', 'CICLO', 'MATERIAL', 'EXAMEN', 'OTRO'])],
            'modalidad_aplicable' => ['required', Rule::in(['ESCOLAR', 'ACADEMIA', 'AMBOS'])],
            'monto_referencial' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'tipo' => 'tipo',
            'modalidad_aplicable' => 'modalidad aplicable',
            'monto_referencial' => 'monto referencial',
            'estado' => 'estado',
        ];
    }
}
