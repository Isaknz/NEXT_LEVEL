@extends('layouts.app')
@section('title', 'Caja ' . $caja->codigo . ' - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cajas.index') }}" class="hover:text-blue-700">Cajas</a></li>
    <li class="breadcrumb-separator">{{ $caja->codigo }}</li>
@endsection
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-cash-register mr-2 text-blue-600"></i>{{ $caja->nombre }}
        </h2>
        <p class="text-gray-500 text-sm mt-1">{{ $caja->codigo }} · {{ $caja->movimientos_count }} movimiento(s) registrados</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('cajas.edit', $caja->id_caja) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        @if($caja->estado !== 'CERRADA')
            <a href="{{ route('cajas.ajuste.crear', $caja->id_caja) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-sliders-h mr-1"></i> Registrar Ajuste
            </a>
        @else
            <form action="{{ route('cajas.aperturar', $caja->id_caja) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                    <i class="fas fa-lock-open mr-1"></i> Reabrir Caja
                </button>
            </form>
        @endif
        <a href="{{ route('cajas.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm hover:bg-gray-600 transition">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
</div>

@if($caja->estado === 'CERRADA')
    <div class="bg-red-50 border border-red-300 rounded-xl p-4 mb-6 text-sm text-red-800">
        <i class="fas fa-lock mr-1"></i>
        Esta caja fue cerrada el {{ $caja->fecha_cierre?->format('d/m/Y \a \l\a\s H:i') }}
        con un saldo final de <strong>S/ {{ number_format((float) $caja->monto_cierre, 2) }}</strong>.
        No se pueden registrar nuevos movimientos.
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-sm text-gray-500">Tipo</p>
        <p class="text-lg font-semibold text-gray-800">{{ str_replace('_', ' ', $caja->tipo) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-sm text-gray-500">Saldo inicial</p>
        <p class="text-lg font-semibold text-gray-800">S/ {{ number_format((float) $caja->saldo_inicial, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-sm text-gray-500">Ingresos</p>
        <p class="text-lg font-semibold text-green-600">+ S/ {{ number_format($ingresos, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-sm text-gray-500">Egresos</p>
        <p class="text-lg font-semibold text-red-600">- S/ {{ number_format($egresos, 2) }}</p>
    </div>
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow p-4 text-white col-span-2 lg:col-span-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-blue-100">Saldo actual calculado</p>
            <p class="text-2xl font-bold">S/ {{ number_format((float) $caja->saldo_inicial + $ingresos - $egresos, 2) }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider">Fecha</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider">Tipo</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider">Origen</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider">Descripción</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold uppercase tracking-wider">Registrado por</th>
                    <th class="text-right py-3 px-4 text-xs font-semibold uppercase tracking-wider">Monto</th>
                    <th class="text-center py-3 px-4 text-xs font-semibold uppercase tracking-wider">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($movimientos as $mov)
                    <tr class="hover:bg-blue-50 transition">
                        <td class="py-3 px-4 text-gray-600">{{ $mov->fecha_movimiento?->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4">
                            @php
                                $esIngreso = in_array($mov->tipo, ['INGRESO', 'AJUSTE_INGRESO'], true);
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $esIngreso ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $mov->tipo }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $mov->origen }}</td>
                        <td class="py-3 px-4 text-gray-700">{{ $mov->descripcion ?: ($mov->concepto ?: 'Sin descripción') }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $mov->registradoPor?->nombre ?? 'Sistema' }}</td>
                        <td class="py-3 px-4 text-right font-semibold {{ ($esIngreso ? 'text-green-600' : 'text-red-600') }}">
                            {{ $esIngreso ? '+' : '-' }} S/ {{ number_format((float) $mov->monto, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $mov->estado === 'ACTIVO' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $mov->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
                                <p class="text-gray-500">Esta caja no tiene movimientos registrados.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $movimientos->appends(request()->query())->links() }}
</div>
@endsection
