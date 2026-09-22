<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', Rule::unique('pagos', 'codigo')],
            'id_matricula' => 'required|exists:matriculas,id_matricula',
            'id_caja' => 'required|exists:cajas,id_caja',
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|in:EFECTIVO,YAPE,PLIN,TRANSFERENCIA,TARJETA,OTRO',
            'numero_operacion' => 'nullable|string|max:100',
            'monto_total' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500',
            'cuentas' => 'required|array|min:1',
            'cuentas.*.id_cuenta' => 'required|exists:cuentas_por_cobrar,id_cuenta',
            'cuentas.*.monto' => 'required|numeric|min:0.01',
        ];
    }
}