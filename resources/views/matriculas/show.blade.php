@extends('layouts.app')

@section('title', 'Detalle Matrícula - Next Level')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>Detalle de Matrícula
            </h2>
            <p class="text-gray-500 text-sm mt-1">Código: {{ $matricula->codigo }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('matriculas.edit', $matricula->id_matricula) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <a href="{{ route('matriculas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Encabezado -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <i class="fas {{ $matricula->modalidad === 'ACADEMIA' ? 'fa-university' : 'fa-user-graduate' }} text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold">{{ $matricula->alumno->apellidos ?? 'N/A' }}, {{ $matricula->alumno->nombres ?? '' }}</h3>
                        <p class="text-indigo-100 mt-1">DNI: {{ $matricula->alumno->dni ?? 'N/A' }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            @php
                                $estadoColor = match($matricula->estado) {
                                    'ACTIVA' => 'green',
                                    'PENDIENTE' => 'yellow',
                                    'RETIRADA' => 'red',
                                    'ANULADA' => 'gray',
                                    'FINALIZADA' => 'blue',
                                    'PAGADA' => 'emerald',
                                    default => 'gray',
                                };
                            @endphp
                            <span class="bg-{{ $estadoColor }}-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $matricula->estado }}
                            </span>
                            <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $matricula->modalidad }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-indigo-100">Fecha de Matrícula</p>
                    <p class="text-2xl font-bold">{{ $matricula->fecha_matricula ? $matricula->fecha_matricula->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Información -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Información Académica</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Periodo:</span>
                            <span class="text-sm font-medium">{{ $matricula->periodo->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Nivel:</span>
                            <span class="text-sm font-medium">{{ $matricula->nivel->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Grado:</span>
                            <span class="text-sm font-medium">{{ $matricula->grado->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Tipo:</span>
                            <span class="text-sm font-medium">{{ $matricula->tipo_matricula }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Resumen Financiero</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Pagado:</span>
                            <span class="text-sm font-bold text-green-600">S/. {{ number_format($matricula->total_pagado, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Deuda Pendiente:</span>
                            <span class="text-sm font-bold text-red-600">S/. {{ number_format($matricula->total_deuda, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Registrado por:</span>
                            <span class="text-sm font-medium">{{ $matricula->registradoPor->nombre ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($matricula->observaciones)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Observaciones</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">{{ $matricula->observaciones }}</p>
                </div>
            </div>
            @endif

            <!-- Cuentas por Cobrar -->
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">
                    <i class="fas fa-file-invoice-dollar text-indigo-600 mr-2"></i>Cuentas por Cobrar
                </h4>
                @if($matricula->cuentasPorCobrar && $matricula->cuentasPorCobrar->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Concepto</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Referencia</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Monto</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Vencimiento</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($matricula->cuentasPorCobrar as $cuenta)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $cuenta->concepto->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $cuenta->referencia }}</td>
                                    <td class="px-4 py-2 text-sm font-semibold">S/. {{ number_format($cuenta->monto_original, 2) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $cuenta->fecha_vencimiento ? $cuenta->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="bg-{{ $cuenta->estado === 'PAGADA' ? 'green' : ($cuenta->estado === 'PENDIENTE' ? 'yellow' : 'gray') }}-100 text-{{ $cuenta->estado === 'PAGADA' ? 'green' : ($cuenta->estado === 'PENDIENTE' ? 'yellow' : 'gray') }}-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            {{ $cuenta->estado }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 bg-gray-50 rounded-lg">
                        <i class="fas fa-file-invoice-dollar text-gray-300 text-3xl mb-2"></i>
                        <p class="text-gray-500 text-sm">No hay cuentas por cobrar</p>
                    </div>
                @endif
            </div>

            <!-- Pagos -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-3">
                    <i class="fas fa-money-bill text-green-600 mr-2"></i>Historial de Pagos
                </h4>
                @if($matricula->pagos && $matricula->pagos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Método</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Monto</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($matricula->pagos as $pago)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-mono">{{ $pago->codigo }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $pago->metodo_pago }}</td>
                                    <td class="px-4 py-2 text-sm font-semibold text-green-600">S/. {{ number_format($pago->monto_total, 2) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="bg-{{ $pago->estado === 'CONFIRMADO' ? 'green' : 'red' }}-100 text-{{ $pago->estado === 'CONFIRMADO' ? 'green' : 'red' }}-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            {{ $pago->estado }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6 bg-gray-50 rounded-lg">
                        <i class="fas fa-money-bill text-gray-300 text-3xl mb-2"></i>
                        <p class="text-gray-500 text-sm">No hay pagos registrados</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
