@extends('layouts.app')
@section('title', 'Ajuste de Caja - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cajas.index') }}" class="hover:text-blue-700">Cajas</a></li>
    <li class="breadcrumb-separator"><a href="{{ route('cajas.show', $caja->id_caja) }}" class="hover:text-blue-700">{{ $caja->nombre }}</a></li>
    <li class="breadcrumb-separator">Ajuste</li>
@endsection
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-coins mr-2 text-blue-600"></i>Ajuste de Caja
        </h2>
        <p class="text-gray-500 text-sm mt-1">Caja: {{ $caja->nombre }} | Saldo actual: S/. {{ number_format($caja->saldo_inicial, 2) }}</p>
    </div>

    @if(session('status'))
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded-r-lg shadow">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-3"></i>
                <span class="font-medium">{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('cajas.ajuste', $caja->id_caja) }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Ajuste *</label>
                <select name="tipo" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Seleccione un tipo</option>
                    <option value="INGRESO">Ingreso</option>
                    <option value="EGRESO">Egreso</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Monto *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">S/.</span>
                    <input type="number" name="monto" required step="0.01" min="0.01"
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0.00">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Concepto</label>
                <input type="text" name="concepto"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ej: Ajuste por error, ingreso extra, etc.">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Descripción del ajuste..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('cajas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow">
                    <i class="fas fa-save mr-1"></i> Registrar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
