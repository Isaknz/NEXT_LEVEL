@extends('layouts.app')
@section('title', 'Grados - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('grados.index') }}" class="hover:text-blue-700">Grados</a></li>
@endsection
@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-graduation-cap mr-2 text-blue-600"></i>Lista de Grados
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $grados->total() }} grado(s) registrado(s)</p>
    </div>
    <a href="{{ route('grados.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nuevo Grado
    </a>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nivel</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Orden</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Alumnos</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($grados as $grado)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        @if($grado->nivel)
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $grado->nivel->nombre }}
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">Nivel eliminado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-800">{{ $grado->nombre }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $grado->orden }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $grado->alumnos_count }}
                        <span class="text-xs text-gray-400">/ {{ $grado->matriculas_count }} mat.</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($grado->estado === 'ACTIVO')
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
                            <a href="{{ route('grados.edit', $grado->id_grado) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('grados.destroy', $grado->id_grado) }}" method="POST"
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
                                        <p class="font-semibold">¿Seguro que deseas eliminar el grado «{{ $grado->nombre }}»?</p>
                                        <p class="text-sm text-gray-500 mt-1">Solo es posible si no tiene alumnos ni matrículas asociadas.</p>
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
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-graduation-cap text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg font-medium">No hay grados registrados</p>
                            <a href="{{ route('grados.create') }}" class="mt-3 text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Crear el primer grado
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
    {{ $grados->appends(request()->query())->links() }}
</div>
@endsection
