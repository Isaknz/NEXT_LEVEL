<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CicloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $ciclo = $this->route('ciclo');

        $unicidad = Rule::unique('ciclos_academia', 'nombre')
            ->ignore($ciclo?->id_ciclo, 'id_ciclo')
            ->where(fn ($q) => $q
                ->where('id_periodo', $this->input('id_periodo'))
                ->where('id_facultad', $this->input('id_facultad'))
                ->where('turno', $this->input('turno')));

        return [
            'id_periodo' => ['required', 'exists:periodos_academicos,id_periodo'],
            'id_facultad' => ['required', 'exists:facultades,id_facultad'],
            'nombre' => ['required', 'string', 'max:120', $unicidad],
            'turno' => ['required', Rule::in(['MANANA', 'TARDE', 'NOCHE', 'COMPLETO'])],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'monto_referencial' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'vacantes' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'estado' => ['required', Rule::in(['PLANIFICADO', 'ABIERTO', 'CERRADO', 'CANCELADO'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'id_periodo' => 'período',
            'id_facultad' => 'facultad',
            'nombre' => 'nombre',
            'turno' => 'turno',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'monto_referencial' => 'monto referencial',
            'vacantes' => 'vacantes',
            'estado' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un ciclo con ese nombre, turno, período y facultad.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
