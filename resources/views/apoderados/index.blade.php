@extends('layouts.app')

@section('title', 'Apoderados - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-users mr-2 text-green-600"></i>Lista de Apoderados
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $apoderados->total() }} apoderados registrados</p>
    </div>
    <a href="{{ route('apoderados.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nuevo Apoderado
    </a>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-green-600 mr-2"></i>Filtros de búsqueda
    </h3>
    <form method="GET" action="{{ route('apoderados.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-search mr-1"></i>Buscar
            </label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Nombre, DNI o celular..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-toggle-on mr-1"></i>Estado
            </label>
            <select name="estado" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                <option value="">Todos</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('apoderados.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
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
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">DNI</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Apoderado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Celular</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Hijos</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($apoderados as $apoderado)
                <tr class="hover:bg-green-50 transition">
                    <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $apoderado->dni ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $apoderado->apellidos }}, {{ $apoderado->nombres }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($apoderado->celular)
                            <i class="fas fa-phone text-green-500 mr-1"></i>{{ $apoderado->celular }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $apoderado->email ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                            <i class="fas fa-child mr-1"></i>{{ $apoderado->alumnos_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($apoderado->estado === 'ACTIVO')
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                ACTIVO
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                INACTIVO
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('apoderados.show', $apoderado->id_apoderado) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('apoderados.edit', $apoderado->id_apoderado) }}" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('apoderados.destroy', $apoderado->id_apoderado) }}" method="POST" 
                                  x-data="{ confirmDelete: false }" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="confirmDelete = true"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div x-show="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div class="bg-white rounded-lg p-6 max-w-sm">
                                        <p class="font-semibold">¿Seguro que deseas eliminar este apoderado?</p>
                                        <div class="mt-4 flex gap-2">
                                            <button @click="confirmDelete = false" class="btn-secondary rounded px-3 py-1">Cancelar</button>
                                            <button type="submit" @click="confirmDelete = false" class="bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">Confirmar</button>
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
                        <i class="fas fa-users text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">No se encontraron apoderados</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $apoderados->appends(request()->query())->links() }}
</div>
@endsection
