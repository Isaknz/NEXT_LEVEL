@extends('layouts.app')
@section('title', 'Editar Categoría - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('categorias.index') }}" class="hover:text-blue-700">Categorías</a></li>
    <li class="breadcrumb-separator">Editar</li>
@endsection
@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Categoría</h2>
    <form method="POST" action="{{ route('categorias.update', $categoria) }}" class="space-y-4">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $categoria->nombre ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('nombre')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
            <input type="text" name="estado" value="{{ old('estado', $categoria->estado ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('estado')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary rounded-lg px-4 py-2 font-semibold text-white">
            <i class="fas fa-save"></i> Guardar
        </button>
    </form>
</div>
@endsection