@extends('layouts.app')
@section('title', 'Niveles - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('niveles.index') }}" class="hover:text-blue-700">Niveles</a></li>
@endsection
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-layer-group mr-2 text-blue-600"></i>Lista de Niveles
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $niveles->total() }} nivel(es) registrado(s)</p>
    </div>
    <a href="{{ route('niveles.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nuevo Nivel
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Código</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grados</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($niveles as $nivel)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono font-semibold text-gray-600">{{ $nivel->codigo }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-800">{{ $nivel->nombre }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $nivel->grados_count }}</td>
                    <td class="px-6 py-4">
                        @if($nivel->estado === 'ACTIVO')
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1 text-green-500"></i>ACTIVO
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1 text-red-500"></i>INACTIVO
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('niveles.edit', $nivel->id_nivel) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('niveles.destroy', $nivel->id_nivel) }}" method="POST"
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
                                        <p class="font-semibold">¿Seguro que deseas eliminar el nivel «{{ $nivel->nombre }}»?</p>
                                        <p class="text-sm text-gray-500 mt-1">Solo es posible si no tiene grados ni matrículas asociadas.</p>
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
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-layer-group text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg font-medium">No hay niveles registrados</p>
                            <a href="{{ route('niveles.create') }}" class="mt-3 text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Crear el primer nivel
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
    {{ $niveles->appends(request()->query())->links() }}
</div>
@endsection
