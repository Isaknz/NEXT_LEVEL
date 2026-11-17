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
            // La protección contra editar una caja CERRADA vive en el
            // controlador; la regla unique anterior sobre id_caja nunca
            // podía fallar (la PK ya es única) y no aportaba nada.
            'estado' => ['required', Rule::in(['ACTIVA', 'INACTIVA'])],
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
        return [];
    }
}
