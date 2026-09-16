@extends('layouts.app')

@section('title', 'Detalle Pago - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-receipt mr-2 text-green-600"></i>Detalle del Pago
            </h2>
            <p class="text-gray-500 text-sm mt-1">Código: {{ $pago->codigo }}</p>
        </div>
        <div class="flex gap-2">
            @if($pago->estado === 'CONFIRMADO')
            <button type="button" onclick="document.getElementById('modal-anular').classList.remove('hidden')"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-ban mr-1"></i> Anular
            </button>
            @endif
            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Encabezado -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-money-bill-wave text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold">S/. {{ number_format($pago->monto_total, 2) }}</h3>
                        <p class="text-green-100 mt-1">Código: {{ $pago->codigo }}</p>
                        <div class="mt-2">
                            @if($pago->estado === 'CONFIRMADO')
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>CONFIRMADO
                                </span>
                            @else
                                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    <i class="fas fa-ban mr-1"></i>ANULADO
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-green-100">Fecha de Pago</p>
                    <p class="text-xl font-bold">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/A' }}</p>
                    <p class="text-sm">{{ $pago->fecha_pago ? $pago->fecha_pago->format('H:i') : '' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Alumno -->
            <div class="mb-6 pb-6 border-b">
                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Alumno</h4>
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-800">
                            {{ $pago->matricula->alumno->apellidos ?? 'N/A' }}, {{ $pago->matricula->alumno->nombres ?? '' }}
                        </p>
                        <p class="text-sm text-gray-500">DNI: {{ $pago->matricula->alumno->dni ?? 'N/A' }} | Matrícula: {{ $pago->matricula->codigo ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Info del pago -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase mb-1">Caja</p>
                    <p class="font-semibold">{{ $pago->caja->nombre ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $pago->caja->tipo ?? '' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase mb-1">Método de Pago</p>
                    <p class="font-semibold">{{ $pago->metodo_pago }}</p>
                    @if($pago->numero_operacion)
                        <p class="text-xs text-gray-500">Op: {{ $pago->numero_operacion }}</p>
                    @endif
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase mb-1">Registrado por</p>
                    <p class="font-semibold">{{ $pago->registradoPor->nombre ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500">{{ $pago->created_at ? $pago->created_at->format('d/m/Y H:i') : '' }}</p>
                </div>
            </div>

            <!-- Comprobante -->
            @if($pago->comprobante)
            <div class="mb-6 pb-6 border-b">
                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Comprobante</h4>
                <div class="flex items-center justify-between bg-blue-50 rounded-lg p-4">
                    <div>
                        <p class="font-semibold text-blue-800">{{ $pago->comprobante->tipo }}</p>
                        <p class="text-sm text-blue-600">
                            {{ $pago->comprobante->serie }}-{{ $pago->comprobante->numero }}
                        </p>
                    </div>
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-print mr-1"></i> Imprimir
                    </button>
                </div>
            </div>
            @endif

            <!-- Detalles del pago -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">
                    <i class="fas fa-list text-green-600 mr-2"></i>Conceptos Pagados
                </h4>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Concepto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Referencia</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Monto Aplicado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pago->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $detalle->cuentaPorCobrar->concepto->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $detalle->cuentaPorCobrar->referencia ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">
                                    S/. {{ number_format($detalle->monto_aplicado, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-green-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right font-bold text-gray-700">TOTAL:</td>
                                <td class="px-4 py-3 text-right font-bold text-green-700 text-lg">
                                    S/. {{ number_format($pago->monto_total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Observaciones -->
            @if($pago->observaciones)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Observaciones</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">{{ $pago->observaciones }}</p>
                </div>
            </div>
            @endif

            <!-- Info de anulación -->
            @if($pago->estado === 'ANULADO')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-red-800 uppercase mb-2">
                    <i class="fas fa-ban mr-1"></i>Información de Anulación
                </h4>
                <p class="text-sm text-red-700">
                    <strong>Anulado por:</strong> {{ $pago->anuladoPor->nombre ?? 'N/A' }}<br>
                    <strong>Fecha:</strong> {{ $pago->anulado_at ? $pago->anulado_at->format('d/m/Y H:i') : 'N/A' }}<br>
                    <strong>Motivo:</strong> {{ $pago->motivo_anulacion }}
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Anular -->
@if($pago->estado === 'CONFIRMADO')
<div id="modal-anular" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Anular Pago</h3>
                <p class="text-sm text-gray-500">{{ $pago->codigo }}</p>
            </div>
        </div>

        <form action="{{ route('pagos.anular', $pago->id_pago) }}" method="POST">
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
