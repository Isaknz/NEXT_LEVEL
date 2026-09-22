@extends('layouts.app')

@section('title', 'Pagos - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-money-bill mr-2 text-green-600"></i>Gestión de Pagos
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $pagos->total() }} pagos registrados</p>
    </div>
    <a href="{{ route('pagos.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Registrar Pago
    </a>
</div>

<!-- Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Pagos</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-receipt text-gray-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total del Mes</p>
                <p class="text-2xl font-bold text-green-600">S/. {{ number_format($stats['total_mes'], 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-check text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Hoy</p>
                <p class="text-2xl font-bold text-blue-600">S/. {{ number_format($stats['total_hoy'], 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Anulados</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($stats['anulados']) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-ban text-red-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-green-600 mr-2"></i>Filtros de búsqueda
    </h3>
    <form method="GET" action="{{ route('pagos.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-600 mb-1">Buscar</label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Código, alumno, operación..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Caja</label>
            <select name="caja" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white text-sm">
                <option value="">Todas</option>
                @foreach($cajas as $caja)
                    <option value="{{ $caja->id_caja }}" {{ request('caja') == $caja->id_caja ? 'selected' : '' }}>
                        {{ $caja->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Método</label>
            <select name="metodo" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white text-sm">
                <option value="">Todos</option>
                <option value="EFECTIVO" {{ request('metodo') === 'EFECTIVO' ? 'selected' : '' }}>Efectivo</option>
                <option value="YAPE" {{ request('metodo') === 'YAPE' ? 'selected' : '' }}>Yape</option>
                <option value="PLIN" {{ request('metodo') === 'PLIN' ? 'selected' : '' }}>Plin</option>
                <option value="TRANSFERENCIA" {{ request('metodo') === 'TRANSFERENCIA' ? 'selected' : '' }}>Transferencia</option>
                <option value="TARJETA" {{ request('metodo') === 'TARJETA' ? 'selected' : '' }}>Tarjeta</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
        </div>

        <div class="md:col-span-6 flex items-end gap-2">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-times mr-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-green-600 to-emerald-600 text-white">
                <tr>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Código</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Alumno</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Fecha</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Caja</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Método</th>
                    <th class="px-4 py-4 text-right text-xs font-semibold uppercase">Monto</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Estado</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($pagos as $pago)
                <tr class="hover:bg-green-50 transition">
                    <td class="px-4 py-3">
                        <span class="text-sm font-mono font-semibold text-gray-700">{{ $pago->codigo }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-2 bg-blue-100">
                                <i class="fas fa-user text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $pago->matricula->alumno->apellidos ?? 'N/A' }}, {{ $pago->matricula->alumno->nombres ?? '' }}
                                </p>
                                <p class="text-xs text-gray-500">Mat: {{ $pago->matricula->codigo ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-medium">
                            {{ $pago->caja->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $metodoIcon = match($pago->metodo_pago) {
                                'EFECTIVO' => 'fa-money-bill',
                                'YAPE' => 'fa-mobile-alt',
                                'PLIN' => 'fa-mobile-alt',
                                'TRANSFERENCIA' => 'fa-university',
                                'TARJETA' => 'fa-credit-card',
                                default => 'fa-money-bill',
                            };
                        @endphp
                        <span class="text-xs text-gray-700">
                            <i class="fas {{ $metodoIcon }} mr-1"></i>{{ $pago->metodo_pago }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-bold text-green-600">S/. {{ number_format($pago->monto_total, 2) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($pago->estado === 'CONFIRMADO')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-check-circle mr-1"></i>CONFIRMADO
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-ban mr-1"></i>ANULADO
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('pagos.show', $pago->id_pago) }}"
                               class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @if($pago->estado === 'CONFIRMADO')
                            <form method="POST" action="{{ route('pagos.anular', $pago->id_pago) }}" 
                                  x-data="{ confirmDelete: false }" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="confirmDelete = true"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition" title="Anular">
                                    <i class="fas fa-ban text-xs"></i>
                                </button>
                                <div x-show="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div class="bg-white rounded-lg p-6 max-w-sm">
                                        <p class="font-semibold">¿Seguro que deseas anular el pago {{ $pago->codigo }}?</p>
                                        <div class="mt-4 flex gap-2">
                                            <button @click="confirmDelete = false" class="btn-secondary rounded px-3 py-1">Cancelar</button>
                                            <button type="submit" @click="confirmDelete = false" class="bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">Confirmar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <i class="fas fa-money-bill text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">No se encontraron pagos</p>
                        <p class="text-gray-400 text-sm">Intenta ajustar los filtros de búsqueda</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $pagos->appends(request()->query())->links() }}
</div>

@endsection
