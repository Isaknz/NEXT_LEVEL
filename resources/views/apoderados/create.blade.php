@extends('layouts.app')

@section('title', 'Nuevo Apoderado - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-user-plus mr-2 text-green-600"></i>Registrar Nuevo Apoderado
    </h2>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
                <span class="font-bold">Por favor corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('apoderados.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">DNI</label>
                <input type="text" name="dni" value="{{ old('dni') }}" maxlength="8" placeholder="8 dígitos"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nombres *</label>
                <input type="text" name="nombres" value="{{ old('nombres') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Apellidos *</label>
                <input type="text" name="apellidos" value="{{ old('apellidos') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Celular</label>
                <input type="text" name="celular" value="{{ old('celular') }}" placeholder="999888777"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                <select name="estado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('apoderados.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Guardar Apoderado
            </button>
        </div>
    </form>
</div>
@endsection
