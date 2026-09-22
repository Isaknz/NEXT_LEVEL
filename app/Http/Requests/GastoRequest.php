<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gasto = $this->route('gasto');

        return [
            'codigo' => [
                'required',
                'string',
                'max:40',
                Rule::unique('gastos', 'codigo')->ignore($gasto?->id_gasto, 'id_gasto'),
            ],
            'id_categoria_gasto' => 'required|exists:categorias_gasto,id_categoria_gasto',
            'id_caja' => 'required|exists:cajas,id_caja',
            'fecha_gasto' => 'required|date',
            'proveedor' => 'nullable|string|max:150',
            'concepto' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:500',
            'monto' => 'required|numeric|min:0.01',
            'tipo_comprobante' => 'required|in:BOLETA,FACTURA,RECIBO,NOTA,SIN_COMPROBANTE',
            'serie_comprobante' => 'nullable|string|max:10',
            'numero_comprobante' => 'nullable|string|max:50',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }
}