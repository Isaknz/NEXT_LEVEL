@extends('layouts.app')

@section('title', 'Usuarios - Next Level')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-user-cog mr-2 text-purple-600"></i>Gestión de Usuarios
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $users->total() }} usuarios registrados</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nuevo Usuario
    </a>
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-filter text-purple-600 mr-2"></i>Filtros de búsqueda
    </h3>
    <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Buscar</label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Nombre o email..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Rol</label>
            <select name="role" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                <option value="">Todos los roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="gerente" {{ request('role') === 'gerente' ? 'selected' : '' }}>Gerente</option>
                <option value="secretaria" {{ request('role') === 'secretaria' ? 'selected' : '' }}>Secretaria</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">Estado</label>
            <select name="estado" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-times mr-1"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Nombre</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Rol</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Creado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-purple-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center mr-3
                                {{ $user->role === 'admin' ? 'bg-purple-100' : ($user->role === 'gerente' ? 'bg-blue-100' : 'bg-green-100') }}">
                                <i class="fas
                                    {{ $user->role === 'admin' ? 'fa-crown text-purple-600' : ($user->role === 'gerente' ? 'fa-user-tie text-blue-600' : 'fa-user text-green-600') }}
                                    text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $user->nombre }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @if($user->role === 'admin')
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-crown mr-1"></i>Admin
                            </span>
                        @elseif($user->role === 'gerente')
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-user-tie mr-1"></i>Gerente
                            </span>
                        @else
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-user mr-1"></i>Secretaria
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($user->estado === 'activo')
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1 text-green-500"></i>ACTIVO
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1 text-red-500"></i>INACTIVO
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('users.show', $user->id) }}"
                               class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $user->id) }}"
                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" 
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
                                        <p class="font-semibold">¿Seguro que deseas eliminar este usuario?</p>
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
                    <td colspan="6" class="px-6 py-12 text-center">
                        <i class="fas fa-user-cog text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">No se encontraron usuarios</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $users->appends(request()->query())->links() }}
</div>
@endsection
