@extends('layouts.app')

@section('title', 'Detalle Gasto - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-receipt mr-2 text-red-600"></i>Detalle del Gasto
            </h2>
            <p class="text-gray-500 text-sm mt-1">Código: {{ $gasto->codigo }}</p>
        </div>
        <div class="flex gap-2">
            @if($gasto->estado === 'REGISTRADO')
            <a href="{{ route('gastos.edit', $gasto->id_gasto) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <button type="button" onclick="document.getElementById('modal-anular').classList.remove('hidden')"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-ban mr-1"></i> Anular
            </button>
            @endif
            <a href="{{ route('gastos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Encabezado -->
        <div class="bg-gradient-to-r from-red-600 to-orange-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-receipt text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold">S/. {{ number_format($gasto->monto, 2) }}</h3>
                        <p class="text-red-100 mt-1">{{ $gasto->concepto }}</p>
                        <div class="mt-2">
                            @if($gasto->estado === 'REGISTRADO')
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    REGISTRADO
                                </span>
                            @else
                                <span class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    ANULADO
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-red-100">Fecha del Gasto</p>
                    <p class="text-xl font-bold">{{ $gasto->fecha_gasto ? $gasto->fecha_gasto->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Info general -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Información del Gasto</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Categoría:</span>
                            <span class="text-sm font-medium">{{ $gasto->categoria->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Proveedor:</span>
                            <span class="text-sm font-medium">{{ $gasto->proveedor ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Caja:</span>
                            <span class="text-sm font-medium">{{ $gasto->caja->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Registrado por:</span>
                            <span class="text-sm font-medium">{{ $gasto->registradoPor->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Fecha de registro:</span>
                            <span class="text-sm font-medium">{{ $gasto->created_at ? $gasto->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Comprobante</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Tipo:</span>
                            <span class="text-sm font-medium">{{ $gasto->tipo_comprobante }}</span>
                        </div>
                        @if($gasto->serie_comprobante)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Serie:</span>
                            <span class="text-sm font-medium">{{ $gasto->serie_comprobante }}</span>
                        </div>
                        @endif
                        @if($gasto->numero_comprobante)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Número:</span>
                            <span class="text-sm font-medium">{{ $gasto->numero_comprobante }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            @if($gasto->descripcion)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Descripción</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">{{ $gasto->descripcion }}</p>
                </div>
            </div>
            @endif

            <!-- Info de anulación -->
            @if($gasto->estado === 'ANULADO')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-red-800 uppercase mb-2">
                    <i class="fas fa-ban mr-1"></i>Información de Anulación
                </h4>
                <p class="text-sm text-red-700">
                    <strong>Anulado por:</strong> {{ $gasto->anuladoPor->nombre ?? 'N/A' }}<br>
                    <strong>Fecha:</strong> {{ $gasto->anulado_at ? $gasto->anulado_at->format('d/m/Y H:i') : 'N/A' }}<br>
                    <strong>Motivo:</strong> {{ $gasto->motivo_anulacion }}
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Anular -->
@if($gasto->estado === 'REGISTRADO')
<div id="modal-anular" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Anular Gasto</h3>
                <p class="text-sm text-gray-500">{{ $gasto->codigo }}</p>
            </div>
        </div>

        <form action="{{ route('gastos.anular', $gasto->id_gasto) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Motivo *</label>
                <textarea name="motivo_anulacion" rows="3" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-anular').classList.add('hidden')"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Cancelar
                </button>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-ban mr-1"></i> Anular
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
