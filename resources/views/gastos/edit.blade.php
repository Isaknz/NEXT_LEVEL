@extends('layouts.app')

@section('title', 'Editar Gasto - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-edit mr-2 text-yellow-600"></i>Editar Gasto: {{ $gasto->codigo }}
    </h2>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gastos.update', $gasto->id_gasto) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo', $gasto->codigo) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha del Gasto *</label>
                <input type="date" name="fecha_gasto"
                       value="{{ old('fecha_gasto', $gasto->fecha_gasto ? $gasto->fecha_gasto->format('Y-m-d') : date('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Categoría *</label>
                <select name="id_categoria_gasto" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria_gasto }}" {{ old('id_categoria_gasto', $gasto->id_categoria_gasto) == $cat->id_categoria_gasto ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Caja *</label>
                <select name="id_caja" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                    @foreach($cajas as $caja)
                        <option value="{{ $caja->id_caja }}" {{ old('id_caja', $gasto->id_caja) == $caja->id_caja ? 'selected' : '' }}>
                            {{ $caja->nombre }} ({{ $caja->tipo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Proveedor</label>
                <input type="text" name="proveedor" value="{{ old('proveedor', $gasto->proveedor) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Concepto *</label>
                <input type="text" name="concepto" value="{{ old('concepto', $gasto->concepto) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Monto *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">S/.</span>
                    <input type="number" name="monto" value="{{ old('monto', $gasto->monto) }}" step="0.01" min="0.01" required
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Comprobante *</label>
                <select name="tipo_comprobante" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                    <option value="SIN_COMPROBANTE" {{ old('tipo_comprobante', $gasto->tipo_comprobante) === 'SIN_COMPROBANTE' ? 'selected' : '' }}>Sin Comprobante</option>
                    <option value="BOLETA" {{ old('tipo_comprobante', $gasto->tipo_comprobante) === 'BOLETA' ? 'selected' : '' }}>Boleta</option>
                    <option value="FACTURA" {{ old('tipo_comprobante', $gasto->tipo_comprobante) === 'FACTURA' ? 'selected' : '' }}>Factura</option>
                    <option value="RECIBO" {{ old('tipo_comprobante', $gasto->tipo_comprobante) === 'RECIBO' ? 'selected' : '' }}>Recibo</option>
                    <option value="NOTA" {{ old('tipo_comprobante', $gasto->tipo_comprobante) === 'NOTA' ? 'selected' : '' }}>Nota</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Serie</label>
                <input type="text" name="serie_comprobante" value="{{ old('serie_comprobante', $gasto->serie_comprobante) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                <input type="text" name="numero_comprobante" value="{{ old('numero_comprobante', $gasto->numero_comprobante) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('descripcion', $gasto->descripcion) }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
            <a href="{{ route('gastos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Actualizar Gasto
            </button>
        </div>
    </form>
</div>
@endsection
