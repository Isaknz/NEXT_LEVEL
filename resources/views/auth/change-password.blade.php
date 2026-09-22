@extends('layouts.app')
@section('title', 'Cambiar Contraseña - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('dashboard') }}" class="hover:text-blue-700">Inicio</a></li>
    <li class="breadcrumb-separator">Cambiar Contraseña</li>
@endsection
@section('content')
<div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Cambiar Contraseña</h2>
    @if(session('status'))
    <div class="bg-yellow-50 border border-yellow-300 text-yellow-700 p-4 rounded-lg mb-4">{{ session('status') }}</div>
    @endif
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
        <button type="submit" class="btn-primary w-full rounded-lg px-4 py-3 font-semibold text-white">
            <i class="fas fa-lock"></i> Cambiar Contraseña
        </button>
    </form>
</div>
@endsection