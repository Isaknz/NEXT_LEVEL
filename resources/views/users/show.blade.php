@extends('layouts.app')

@section('title', 'Detalle Usuario - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-user mr-2 text-purple-600"></i>Detalle del Usuario
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('users.edit', $user->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold">{{ $user->nombre }}</h3>
                    <p class="text-purple-100 mt-1">{{ $user->email }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        @if($user->role === 'admin')
                            <span class="bg-purple-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-crown mr-1"></i>ADMIN
                            </span>
                        @elseif($user->role === 'gerente')
                            <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-user-tie mr-1"></i>GERENTE
                            </span>
                        @else
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-user mr-1"></i>SECRETARIA
                            </span>
                        @endif

                        @if($user->estado === 'activo')
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">ACTIVO</span>
                        @else
                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">INACTIVO</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Información</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Nombre</p>
                            <p class="font-semibold">{{ $user->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-semibold">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha de registro</p>
                            <p class="font-semibold">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Permisos</h4>
                    <div class="space-y-2">
                        @if($user->role === 'admin')
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Acceso total al sistema</p>
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Gestionar usuarios</p>
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Ver auditoría</p>
                        @elseif($user->role === 'gerente')
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Acceso total al sistema</p>
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Gestionar usuarios</p>
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Ver auditoría</p>
                        @else
                            <p class="text-sm"><i class="fas fa-check-circle text-green-500 mr-2"></i>Ver y agregar datos</p>
                            <p class="text-sm"><i class="fas fa-times-circle text-red-500 mr-2"></i>No puede editar</p>
                            <p class="text-sm"><i class="fas fa-times-circle text-red-500 mr-2"></i>No puede eliminar</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
