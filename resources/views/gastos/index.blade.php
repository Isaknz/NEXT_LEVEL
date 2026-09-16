@extends('layouts.app')

@section('title', 'Gastos - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-receipt mr-2 text-red-600"></i>Gestión de Gastos
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $gastos->total() }} gastos registrados</p>
    </div>
    <a href="{{ route('gastos.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Registrar Gasto
    </a>
</div>

<!-- Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Gastos</p>
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
                <p class="text-2xl font-bold text-red-600">S/. {{ number_format($stats['total_mes'], 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-check text-red-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Hoy</p>
                <p class="text-2xl font-bold text-orange-600">S/. {{ number_format($stats['total_hoy'], 2) }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-orange-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Anulados</p>
                <p class="text-2xl font-bold text-gray-600">{{ number_format($stats['anulados']) }}</p>
            </div>
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-ban text-gray-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-red-600 mr-2"></i>Filtros de búsqueda
    </h3>
    <form method="GET" action="{{ route('gastos.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-600 mb-1">Buscar</label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Código, concepto, proveedor..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Categoría</label>
            <select name="categoria" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white text-sm">
                <option value="">Todas</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id_categoria_gasto }}" {{ request('categoria') == $cat->id_categoria_gasto ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Caja</label>
            <select name="caja" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-white text-sm">
                <option value="">Todas</option>
                @foreach($cajas as $caja)
                    <option value="{{ $caja->id_caja }}" {{ request('caja') == $caja->id_caja ? 'selected' : '' }}>
                        {{ $caja->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
        </div>

        <div class="md:col-span-6 flex items-end gap-2">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('gastos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-times mr-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-red-600 to-orange-600 text-white">
                <tr>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Código</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Fecha</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Concepto</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Categoría</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Caja</th>
                    <th class="px-4 py-4 text-right text-xs font-semibold uppercase">Monto</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Estado</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($gastos as $gasto)
                <tr class="hover:bg-red-50 transition">
                    <td class="px-4 py-3">
                        <span class="text-sm font-mono font-semibold text-gray-700">{{ $gasto->codigo }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $gasto->fecha_gasto ? $gasto->fecha_gasto->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-gray-800">{{ $gasto->concepto }}</p>
                        @if($gasto->proveedor)
                            <p class="text-xs text-gray-500"><i class="fas fa-building mr-1"></i>{{ $gasto->proveedor }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded font-medium">
                            {{ $gasto->categoria->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded font-medium">
                            {{ $gasto->caja->nombre ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-bold text-red-600">S/. {{ number_format($gasto->monto, 2) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($gasto->estado === 'REGISTRADO')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                REGISTRADO
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">
                                ANULADO
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('gastos.show', $gasto->id_gasto) }}"
                               class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @if($gasto->estado === 'REGISTRADO')
                            <a href="{{ route('gastos.edit', $gasto->id_gasto) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <button type="button"
                                    onclick="abrirModalAnular({{ $gasto->id_gasto }}, '{{ $gasto->codigo }}')"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition" title="Anular">
                                <i class="fas fa-ban text-xs"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <i class="fas fa-receipt text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">No se encontraron gastos</p>
                        <p class="text-gray-400 text-sm">Intenta ajustar los filtros de búsqueda</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $gastos->appends(request()->query())->links() }}
</div>

<!-- Modal Anular -->
<div id="modal-anular" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Anular Gasto</h3>
                <p class="text-sm text-gray-500" id="modal-codigo"></p>
            </div>
        </div>

        <form id="form-anular" method="POST">
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

<script>
    function abrirModalAnular(id, codigo) {
        document.getElementById('modal-anular').classList.remove('hidden');
        document.getElementById('modal-codigo').textContent = 'Gasto: ' + codigo;
        document.getElementById('form-anular').action = '/gastos/' + id + '/anular';
    }
</script>
@endsection
