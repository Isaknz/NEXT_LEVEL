<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CuentaPorCobrarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $cuenta = $this->route('cuenta');

        $unicidad = Rule::unique('cuentas_por_cobrar', 'referencia')
            ->ignore($cuenta?->id_cuenta, 'id_cuenta')
            ->where(fn ($q) => $q
                ->where('id_matricula', $this->input('id_matricula'))
                ->where('id_concepto', $this->input('id_concepto')));

        return [
            'id_matricula' => ['required', 'exists:matriculas,id_matricula'],
            'id_concepto' => [
                'required',
                'exists:conceptos_cobro,id_concepto',
                Rule::exists('conceptos_cobro', 'id_concepto')->where(fn ($q) => $q->where('estado', 'ACTIVO')),
            ],
            'referencia' => ['required', 'string', 'max:80', $unicidad],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'monto_original' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'descuento' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'recargo' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id_matricula' => 'matrícula',
            'id_concepto' => 'concepto',
            'referencia' => 'referencia',
            'descripcion' => 'descripción',
            'fecha_emision' => 'fecha de emisión',
            'fecha_vencimiento' => 'fecha de vencimiento',
            'monto_original' => 'monto original',
            'descuento' => 'descuento',
            'recargo' => 'recargo',
        ];
    }

    public function messages(): array
    {
        return [
            'id_concepto.exists' => 'El concepto seleccionado no existe o está inactivo.',
            'referencia.unique' => 'Ya existe una cuenta por cobrar con esa referencia para la matrícula y concepto seleccionados.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
        ];
    }
}
