@extends('layouts.app')

@section('title', 'Auditoría - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-history mr-2 text-gray-600"></i>Registro de Movimientos
        </h2>
        <p class="text-gray-500 text-sm mt-1">Historial de acciones de usuarios</p>
    </div>
    <button onclick="document.getElementById('modal-limpiar').classList.remove('hidden')"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm shadow transition">
        <i class="fas fa-broom mr-1"></i> Limpiar antiguos
    </button>
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
                <i class="fas fa-database text-gray-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Hoy</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['hoy']) }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-day text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Esta semana</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['semana']) }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-week text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Este mes</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['mes']) }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-gray-600 mr-2"></i>Filtros
    </h3>
    <form method="GET" action="{{ route('auditoria.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Usuario</label>
            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 bg-white text-sm">
                <option value="">Todos</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ request('user_id') == $usuario->id ? 'selected' : '' }}>
                        {{ $usuario->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Acción</label>
            <select name="accion" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 bg-white text-sm">
                <option value="">Todas</option>
                @foreach($acciones as $accion)
                    <option value="{{ $accion }}" {{ request('accion') === $accion ? 'selected' : '' }}>
                        {{ $accion }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Módulo</label>
            <select name="modulo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 bg-white text-sm">
                <option value="">Todos</option>
                @foreach($modulos as $modulo)
                    <option value="{{ $modulo }}" {{ request('modulo') === $modulo ? 'selected' : '' }}>
                        {{ $modulo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-gray-700 hover:bg-gray-800 text-white px-3 py-2 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('auditoria.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-2 rounded-lg text-sm shadow transition">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Fecha/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Acción</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Módulo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Descripción</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">IP</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($movimientos as $mov)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <div>
                            <p class="font-medium">{{ $mov->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $mov->created_at->format('H:i:s') }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-2">
                                <i class="fas fa-user text-gray-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ $mov->usuario_nombre }}</p>
                                <p class="text-xs text-gray-400 capitalize">{{ $mov->user->role ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-{{ $mov->accion_color }}-100 text-{{ $mov->accion_color }}-800 px-2 py-1 rounded-full text-xs font-semibold">
                            <i class="fas {{ $mov->accion_icono }} mr-1"></i>{{ $mov->accion }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium">
                            {{ $mov->modulo }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ Str::limit($mov->descripcion, 50) }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">
                        {{ $mov->ip_address ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('auditoria.show', $mov->id_registro) }}"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <i class="fas fa-history text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">No hay movimientos registrados</p>
                        <p class="text-gray-400 text-sm">Las acciones de los usuarios aparecerán aquí</p>
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

<!-- Modal Limpiar -->
<div id="modal-limpiar" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Limpiar registros antiguos</h3>
                <p class="text-sm text-gray-500">Esta acción no se puede deshacer</p>
            </div>
        </div>

        <form action="{{ route('auditoria.limpiar') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Eliminar registros anteriores a:
                </label>
                <input type="date" name="fecha_limite" required max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                       value="{{ date('Y-m-d', strtotime('-30 days')) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-limpiar').classList.add('hidden')"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Cancelar
                </button>
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm"
                        onclick="return confirm('¿Estás seguro de eliminar los registros antiguos?')">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
