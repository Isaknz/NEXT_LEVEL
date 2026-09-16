@extends('layouts.app')

@section('title', 'Registrar Gasto - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-receipt mr-2 text-red-600"></i>Registrar Nuevo Gasto
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

    <form action="{{ route('gastos.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Código -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-barcode text-red-500 mr-1"></i>Código *
                </label>
                <input type="text" name="codigo"
                       value="{{ old('codigo', 'G' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Fecha -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar text-red-500 mr-1"></i>Fecha del Gasto *
                </label>
                <input type="date" name="fecha_gasto"
                       value="{{ old('fecha_gasto', date('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tags text-red-500 mr-1"></i>Categoría *
                </label>
                <select name="id_categoria_gasto" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                    <option value="">Seleccionar categoría</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria_gasto }}" {{ old('id_categoria_gasto') == $cat->id_categoria_gasto ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Caja -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-cash-register text-red-500 mr-1"></i>Caja *
                </label>
                <select name="id_caja" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                    <option value="">Seleccionar caja</option>
                    @foreach($cajas as $caja)
                        <option value="{{ $caja->id_caja }}" {{ old('id_caja') == $caja->id_caja ? 'selected' : '' }}>
                            {{ $caja->nombre }} ({{ $caja->tipo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Proveedor -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-building text-red-500 mr-1"></i>Proveedor
                </label>
                <input type="text" name="proveedor" value="{{ old('proveedor') }}"
                       placeholder="Nombre del proveedor (opcional)"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Concepto -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-comment text-red-500 mr-1"></i>Concepto *
                </label>
                <input type="text" name="concepto" value="{{ old('concepto') }}" required
                       placeholder="Ej: Compra de útiles de limpieza"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Monto -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-money-bill-wave text-red-500 mr-1"></i>Monto *
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">S/.</span>
                    <input type="number" name="monto" value="{{ old('monto') }}" step="0.01" min="0.01" required
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>
            </div>

            <!-- Tipo Comprobante -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-file-invoice text-red-500 mr-1"></i>Tipo de Comprobante *
                </label>
                <select name="tipo_comprobante" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white">
                    <option value="SIN_COMPROBANTE" {{ old('tipo_comprobante') === 'SIN_COMPROBANTE' ? 'selected' : '' }}>Sin Comprobante</option>
                    <option value="BOLETA" {{ old('tipo_comprobante') === 'BOLETA' ? 'selected' : '' }}>Boleta</option>
                    <option value="FACTURA" {{ old('tipo_comprobante') === 'FACTURA' ? 'selected' : '' }}>Factura</option>
                    <option value="RECIBO" {{ old('tipo_comprobante') === 'RECIBO' ? 'selected' : '' }}>Recibo</option>
                    <option value="NOTA" {{ old('tipo_comprobante') === 'NOTA' ? 'selected' : '' }}>Nota</option>
                </select>
            </div>

            <!-- Serie -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Serie</label>
                <input type="text" name="serie_comprobante" value="{{ old('serie_comprobante') }}"
                       placeholder="Ej: F001"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Número -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                <input type="text" name="numero_comprobante" value="{{ old('numero_comprobante') }}"
                       placeholder="Ej: 00012345"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <!-- Descripción -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-align-left text-red-500 mr-1"></i>Descripción
                </label>
                <textarea name="descripcion" rows="3"
                          placeholder="Descripción adicional del gasto (opcional)"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('descripcion') }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
            <a href="{{ route('gastos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Registrar Gasto
            </button>
        </div>
    </form>
</div>
@endsection
