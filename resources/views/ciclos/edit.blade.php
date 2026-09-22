@extends('layouts.app')
@section('title', 'Editar Ciclo - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('ciclos.index') }}" class="hover:text-blue-700">Ciclos</a></li>
    <li class="breadcrumb-separator">Editar</li>
@endsection
@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Ciclo</h2>
    <form method="POST" action="{{ route('ciclos.update', $ciclo) }}" class="space-y-4">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Período</label>
            <input type="text" name="periodo" value="{{ old('periodo', $ciclo->periodo ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('periodo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Facultad</label>
            <input type="text" name="facultad" value="{{ old('facultad', $ciclo->facultad ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('facultad')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $ciclo->nombre ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('nombre')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Inicio</label>
            <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $ciclo->fecha_inicio ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('fecha_inicio')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Fin</label>
            <input type="date" name="fecha_fin" value="{{ old('fecha_fin', $ciclo->fecha_fin ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('fecha_fin')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Horario</label>
            <input type="text" name="horario" value="{{ old('horario', $ciclo->horario ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('horario')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Capacidad</label>
            <input type="text" name="capacidad" value="{{ old('capacidad', $ciclo->capacidad ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('capacidad')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
            <input type="text" name="estado" value="{{ old('estado', $ciclo->estado ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('estado')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary rounded-lg px-4 py-2 font-semibold text-white">
            <i class="fas fa-save"></i> Guardar
        </button>
    </form>
</div>
@endsection