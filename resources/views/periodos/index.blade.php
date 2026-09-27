@extends('layouts.app')
@section('title', 'Periodos - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('periodos.index') }}" class="hover:text-blue-700">Periodos</a></li>
@endsection
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Periodos Académicos
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $periodos->total() }} período(s) registrado(s)</p>
    </div>
    <a href="{{ route('periodos.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nuevo Periodo
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Código</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Año</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Fechas</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Matr./Ciclos</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($periodos as $periodo)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono font-semibold text-gray-600">{{ $periodo->codigo }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-800">{{ $periodo->nombre }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $periodo->anio }}</td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        <div><i class="fas fa-arrow-right text-gray-400 mr-1"></i>{{ $periodo->fecha_inicio?->format('d/m/Y') }}</div>
                        <div class="mt-0.5"><i class="fas fa-arrow-left text-gray-400 mr-1"></i>{{ $periodo->fecha_fin?->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $periodo->matriculas_count }} <span class="text-xs text-gray-400">mat.</span>
                        <span class="text-gray-300">|</span>
                        {{ $periodo->ciclos_count }} <span class="text-xs text-gray-400">cic.</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $estilos = [
                                'PLANIFICADO' => 'bg-gray-100 text-gray-800',
                                'ABIERTO' => 'bg-green-100 text-green-800',
                                'CERRADO' => 'bg-red-100 text-red-800',
                            ];
                            $coloresIcono = [
                                'PLANIFICADO' => 'text-gray-500',
                                'ABIERTO' => 'text-green-500',
                                'CERRADO' => 'text-red-500',
                            ];
                        @endphp
                        <span class="{{ $estilos[$periodo->estado] ?? 'bg-gray-100 text-gray-800' }} px-3 py-1 rounded-full text-xs font-semibold">
                            <i class="fas fa-circle mr-1 {{ $coloresIcono[$periodo->estado] ?? '' }}"></i>{{ $periodo->estado }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('periodos.edit', $periodo->id_periodo) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('periodos.destroy', $periodo->id_periodo) }}" method="POST"
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
                                        <p class="font-semibold">¿Seguro que deseas eliminar el período «{{ $periodo->codigo }}»?</p>
                                        <p class="text-sm text-gray-500 mt-1">Solo es posible si no está cerrado y no tiene matrículas ni ciclos asociados.</p>
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
                            <i class="fas fa-calendar-alt text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg font-medium">No hay períodos registrados</p>
                            <a href="{{ route('periodos.create') }}" class="mt-3 text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Crear el primer período
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
    {{ $periodos->appends(request()->query())->links() }}
</div>
@endsection
