@extends('layouts.app')
@section('title', 'Crear Facultad - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('facultades.index') }}" class="hover:text-blue-700">Facultades</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Crear Facultad</h2>
    <form method="POST" action="{{ route('facultades.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Código</label>
            <input type="text" name="codigo" value="{{ old('codigo', $facultad->codigo ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('codigo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $facultad->nombre ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('nombre')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
            <input type="text" name="descripcion" value="{{ old('descripcion', $facultad->descripcion ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('descripcion')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
            <input type="text" name="estado" value="{{ old('estado', $facultad->estado ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('estado')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary rounded-lg px-4 py-2 font-semibold text-white">
            <i class="fas fa-save"></i> Guardar
        </button>
    </form>
</div>
@endsection