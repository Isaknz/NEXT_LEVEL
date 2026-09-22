@extends('layouts.app')
@section('title', 'Mi Perfil - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('dashboard') }}" class="hover:text-blue-700">Inicio</a></li>
    <li class="breadcrumb-separator">Perfil</li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Mi Perfil</h2>
    <p class="text-gray-500 mt-1">Administra tu información personal y seguridad de cuenta.</p>
</div>
<div class="max-w-2xl space-y-8">
    {{-- Información de Perfil --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Perfil</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                <input type="text" name="nombre" value="{{ auth()->user()->nombre }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Correo electrónico</label>
                <input type="email" name="email" value="{{ auth()->user()->email }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <button type="submit" class="btn-primary rounded-lg px-4 py-2 font-semibold text-white">
                <i class="fas fa-save"></i> Guardar cambios
            </button>
        </form>
    </div>

    {{-- Cambio de Contraseña --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Cambiar Contraseña</h3>
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Contraseña Actual</label>
                <input type="password" name="password_current" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nueva Contraseña</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <button type="submit" class="btn-primary rounded-lg px-4 py-2 font-semibold text-white">
                <i class="fas fa-lock"></i> Actualizar Contraseña
            </button>
        </form>
    </div>

    {{-- Información de Cuenta --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de Cuenta</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Rol</label>
                <input type="text" value="{{ auth()->user()->role }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 capitalize" disabled>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Último Acceso</label>
                <input type="text" value="{{ auth()->user()->last_login ? auth()->user()->last_login->format('d/m/Y H:i') : 'Nunca' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" disabled>
            </div>
        </div>
    </div>
</div>
@endsection