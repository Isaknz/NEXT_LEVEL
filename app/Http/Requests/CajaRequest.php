<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $caja = $this->route('caja');

        return [
            'codigo' => [
                'required', 'string', 'max:30',
                Rule::unique('cajas', 'codigo')->ignore($caja?->id_caja, 'id_caja'),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::in(['EFECTIVO', 'BANCO', 'BILLETERA_DIGITAL'])],
            'saldo_inicial' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'estado' => [
                'required',
                Rule::in(['ACTIVA', 'INACTIVA']),
                Rule::unique('cajas', 'id_caja')->ignore($caja?->id_caja, 'id_caja')->where(fn ($q) => $q->where('estado', 'CERRADA')),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'tipo' => 'tipo',
            'saldo_inicial' => 'saldo inicial',
            'estado' => 'estado',
        ];
    }

    public function messages(): array
    {
        return ['estado.unique' => 'No se puede cambiar el estado de una caja que ya fue cerrada.'];
    }
}
