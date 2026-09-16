@extends('layouts.app')

@section('title', 'Matrículas - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>Gestión de Matrículas
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $matriculas->total() }} matrículas registradas</p>
    </div>
    <a href="{{ route('matriculas.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nueva Matrícula
    </a>
</div>

<!-- Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-file-invoice text-gray-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Activas</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['activas']) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pendientes']) }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Retiradas</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($stats['retiradas']) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-slash text-red-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-indigo-600 mr-2"></i>Filtros de búsqueda
    </h3>
    <form method="GET" action="{{ route('matriculas.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Buscar</label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Código, alumno o DNI..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Periodo</label>
            <select name="periodo" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white text-sm">
                <option value="">Todos</option>
                @foreach($periodos as $periodo)
                    <option value="{{ $periodo->id_periodo }}" {{ request('periodo') == $periodo->id_periodo ? 'selected' : '' }}>
                        {{ $periodo->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Nivel</label>
            <select name="nivel" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white text-sm">
                <option value="">Todos</option>
                @foreach($niveles as $nivel)
                    <option value="{{ $nivel->id_nivel }}" {{ request('nivel') == $nivel->id_nivel ? 'selected' : '' }}>
                        {{ $nivel->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white text-sm">
                <option value="">Todos</option>
                <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                <option value="ACTIVA" {{ request('estado') === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                <option value="RETIRADA" {{ request('estado') === 'RETIRADA' ? 'selected' : '' }}>Retirada</option>
                <option value="ANULADA" {{ request('estado') === 'ANULADA' ? 'selected' : '' }}>Anulada</option>
                <option value="FINALIZADA" {{ request('estado') === 'FINALIZADA' ? 'selected' : '' }}>Finalizada</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('matriculas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <tr>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Código</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Alumno</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Periodo</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Nivel / Grado</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Modalidad</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Fecha</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Estado</th>
                    <th class="px-4 py-4 text-left text-xs font-semibold uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($matriculas as $matricula)
                <tr class="hover:bg-indigo-50 transition">
                    <td class="px-4 py-3">
                        <span class="text-sm font-mono font-semibold text-gray-700">{{ $matricula->codigo }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-2
                                {{ $matricula->modalidad === 'ACADEMIA' ? 'bg-purple-100' : 'bg-blue-100' }}">
                                <i class="fas {{ $matricula->modalidad === 'ACADEMIA' ? 'fa-university text-purple-600' : 'fa-user text-blue-600' }} text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $matricula->alumno->apellidos ?? 'N/A' }}, {{ $matricula->alumno->nombres ?? '' }}
                                </p>
                                <p class="text-xs text-gray-500">DNI: {{ $matricula->alumno->dni ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $matricula->periodo->nombre ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($matricula->modalidad === 'ESCOLAR')
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                {{ $matricula->grado->nombre ?? 'N/A' }} {{ $matricula->nivel->nombre ?? '' }}
                            </span>
                        @else
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-university mr-1"></i>{{ $matricula->ciclo->nombre ?? 'Academia' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($matricula->modalidad === 'ESCOLAR')
                            <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded font-medium">Escolar</span>
                        @else
                            <span class="text-xs bg-purple-50 text-purple-700 px-2 py-1 rounded font-medium">Academia</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $matricula->fecha_matricula ? $matricula->fecha_matricula->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $estadoColor = match($matricula->estado) {
                                'ACTIVA' => 'green',
                                'PENDIENTE' => 'yellow',
                                'RETIRADA' => 'red',
                                'ANULADA' => 'gray',
                                'FINALIZADA' => 'blue',
                                default => 'gray',
                            };
                        @endphp
                        <span class="bg-{{ $estadoColor }}-100 text-{{ $estadoColor }}-800 px-2 py-1 rounded-full text-xs font-semibold">
                            {{ $matricula->estado }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('matriculas.show', $matricula->id_matricula) }}"
                               class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('matriculas.edit', $matricula->id_matricula) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('matriculas.destroy', $matricula->id_matricula) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition"
                                        title="Eliminar"
                                        onclick="return confirm('¿Estás seguro de eliminar esta matrícula?')">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <i class="fas fa-file-invoice text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">No se encontraron matrículas</p>
                        <p class="text-gray-400 text-sm">Intenta ajustar los filtros de búsqueda</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $matriculas->appends(request()->query())->links() }}
</div>
@endsection
