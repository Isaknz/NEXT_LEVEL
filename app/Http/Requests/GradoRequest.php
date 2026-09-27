<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role !== 'cajero';
    }

    public function rules(): array
    {
        $grado = $this->route('grado');
        $idNivel = $this->input('id_nivel');

        $mismoNivel = fn (string $columna) => Rule::unique('grados', $columna)
            ->ignore($grado?->id_grado, 'id_grado')
            ->where(fn ($q) => $q->where('id_nivel', $idNivel));

        return [
            'id_nivel' => ['required', 'exists:niveles,id_nivel'],
            'nombre' => ['required', 'string', 'max:30', $mismoNivel('nombre')],
            'orden' => ['required', 'integer', 'min:1', 'max:255', $mismoNivel('orden')],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'id_nivel' => 'nivel',
            'nombre' => 'nombre',
            'orden' => 'orden',
            'estado' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un grado con ese nombre en el nivel seleccionado.',
            'orden.unique' => 'Ya existe un grado con ese orden en el nivel seleccionado.',
        ];
    }
}
