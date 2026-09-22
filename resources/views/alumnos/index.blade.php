@extends('layouts.app')

@section('title', 'Alumnos - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-user-graduate mr-2 text-blue-600"></i>Lista de Alumnos
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $alumnos->total() }} alumnos registrados</p>
    </div>
    <a href="{{ route('alumnos.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nuevo Alumno
    </a>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-blue-600 mr-2"></i>Filtros de búsqueda
    </h3>
    <form method="GET" action="{{ route('alumnos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-layer-group mr-1"></i>Nivel
            </label>
            <select name="nivel" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Todos los niveles</option>
                @foreach($niveles as $nivel)
                    <option value="{{ $nivel->id_nivel }}" {{ request('nivel') == $nivel->id_nivel ? 'selected' : '' }}>
                        {{ $nivel->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-graduation-cap mr-1"></i>Grado
            </label>
            <select name="grado" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Todos los grados</option>
                @foreach($grados as $grado)
                    <option value="{{ $grado->id_grado }}" {{ request('grado') == $grado->id_grado ? 'selected' : '' }}>
                        {{ $grado->nombre }} - {{ $grado->nivel->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-search mr-1"></i>Buscar
            </label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Nombre, DNI o código..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-times mr-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Código</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">DNI</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Alumno</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Grado / Nivel</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Apoderado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($alumnos as $alumno)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono font-semibold text-gray-600">{{ $alumno->codigo ?? 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $alumno->dni ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-3
                                {{ $alumno->es_academia ? 'bg-purple-100' : 'bg-blue-100' }}">
                                <i class="fas {{ $alumno->es_academia ? 'fa-university text-purple-600' : 'fa-user text-blue-600' }} text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($alumno->grado)
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $alumno->grado->nombre }} {{ $alumno->grado->nivel->nombre }}
                            </span>
                        @elseif($alumno->es_academia)
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-university mr-1"></i>Academia
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">Sin asignar</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        @if($alumno->apoderados->count() > 0)
                            @php
                                $apoderadoPrincipal = $alumno->apoderados->firstWhere('pivot.es_principal', 1) ?? $alumno->apoderados->first();
                            @endphp
                            <div>
                                <p class="font-medium">{{ $apoderadoPrincipal->apellidos }}, {{ $apoderadoPrincipal->nombres }}</p>
                                @if($apoderadoPrincipal->pivot->parentesco)
                                    <span class="text-xs text-gray-500">{{ $apoderadoPrincipal->pivot->parentesco }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">Sin apoderado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($alumno->estado === 'ACTIVO')
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
                            <a href="{{ route('alumnos.show', $alumno->id_alumno) }}"
                               class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('alumnos.edit', $alumno->id_alumno) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('alumnos.destroy', $alumno->id_alumno) }}" method="POST" 
                                  x-data="{ confirmDelete: false }" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="confirmDelete = true"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div x-show="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div class="bg-white rounded-lg p-6 max-w-sm">
                                        <p class="font-semibold">¿Seguro que deseas eliminar este alumno?</p>
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
                        <div class="flex flex-col items-center">
                            <i class="fas fa-user-graduate text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg font-medium">No se encontraron alumnos</p>
                            <p class="text-gray-400 text-sm">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Paginación -->
<div class="mt-6">
    {{ $alumnos->appends(request()->query())->links() }}
</div>
@endsection
