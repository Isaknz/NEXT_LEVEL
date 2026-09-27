@extends('layouts.app')
@section('title', 'Cajas - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cajas.index') }}" class="hover:text-blue-700">Cajas</a></li>
@endsection
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-cash-register mr-2 text-blue-600"></i>Cajas
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $cajas->total() }} caja(s) registrada(s)</p>
    </div>
    <a href="{{ route('cajas.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nueva Caja
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Código</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Saldo Inicial</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Movimientos</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($cajas as $caja)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('cajas.show', $caja->id_caja) }}" class="text-sm font-mono font-semibold text-blue-700 hover:underline">
                            {{ $caja->codigo }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-800">{{ $caja->nombre }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $tipos = [
                                'EFECTIVO' => ['clase' => 'bg-green-100 text-green-800', 'icono' => 'fa-money-bill', 'label' => 'Efectivo'],
                                'BANCO' => ['clase' => 'bg-blue-100 text-blue-800', 'icono' => 'fa-building-columns', 'label' => 'Banco'],
                                'BILLETERA_DIGITAL' => ['clase' => 'bg-purple-100 text-purple-800', 'icono' => 'fa-mobile-screen', 'label' => 'Billetera digital'],
                            ];
                            $tipo = $tipos[$caja->tipo] ?? ['clase' => 'bg-gray-100 text-gray-800', 'icono' => 'fa-wallet', 'label' => $caja->tipo];
                        @endphp
                        <span class="{{ $tipo['clase'] }} px-3 py-1 rounded-full text-xs font-semibold">
                            <i class="fas {{ $tipo['icono'] }} mr-1"></i>{{ $tipo['label'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                        S/ {{ number_format((float) $caja->saldo_inicial, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $caja->movimientos_count }}
                        <span class="text-xs text-gray-400">mov.</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $estilos = [
                                'ACTIVA' => 'bg-green-100 text-green-800',
                                'INACTIVA' => 'bg-gray-100 text-gray-800',
                                'CERRADA' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="{{ $estilos[$caja->estado] ?? 'bg-gray-100 text-gray-800' }} px-3 py-1 rounded-full text-xs font-semibold">
                            <i class="fas fa-circle mr-1"></i>{{ $caja->estado }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('cajas.show', $caja->id_caja) }}"
                               class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver movimientos">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('cajas.edit', $caja->id_caja) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($caja->estado === 'CERRADA')
                                <form action="{{ route('cajas.aperturar', $caja->id_caja) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 p-2 rounded-lg transition" title="Reabrir caja">
                                        <i class="fas fa-lock-open"></i>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('cajas.ajuste.crear', $caja->id_caja) }}"
                                   class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 p-2 rounded-lg transition" title="Registrar ajuste">
                                    <i class="fas fa-sliders-h"></i>
                                </a>
                                <form action="{{ route('cajas.cerrar', $caja->id_caja) }}" method="POST"
                                      x-data="{ confirm: false }" class="inline">
                                    @csrf
                                    <button type="button" @click="confirm = true"
                                            class="bg-orange-100 hover:bg-orange-200 text-orange-700 p-2 rounded-lg transition" title="Cerrar caja">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                    <div x-show="confirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
                                        <div class="bg-white rounded-lg p-6 max-w-sm">
                                            <p class="font-semibold">¿Cerrar la caja «{{ $caja->codigo }}»?</p>
                                            <p class="text-sm text-gray-500 mt-1">Se calculará el saldo final con los movimientos actuales. No se podrán registrar nuevos ingresos ni egresos.</p>
                                            <div class="mt-4 flex gap-2 justify-end">
                                                <button type="button" @click="confirm = false" class="btn-secondary rounded px-3 py-1">Cancelar</button>
                                                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white rounded px-3 py-1">Cerrar caja</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endif
                            <form action="{{ route('cajas.destroy', $caja->id_caja) }}" method="POST"
                                  x-data="{ confirmDelete: false }" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="confirmDelete = true"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div x-show="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
                                    <div class="bg-white rounded-lg p-6 max-w-sm">
                                        <p class="font-semibold">¿Seguro que deseas eliminar la caja «{{ $caja->codigo }}»?</p>
                                        <p class="text-sm text-gray-500 mt-1">Solo es posible si no tiene movimientos, pagos ni gastos registrados.</p>
                                        <div class="mt-4 flex gap-2 justify-end">
                                            <button type="button" @click="confirmDelete = false" class="btn-secondary rounded px-3 py-1">Cancelar</button>
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-cash-register text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg font-medium">No hay cajas registradas</p>
                            <a href="{{ route('cajas.create') }}" class="mt-3 text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Crear la primera caja
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $cajas->appends(request()->query())->links() }}
</div>
@endsection
