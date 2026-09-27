@extends('layouts.app')
@section('title', 'Crear Grado - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('grados.index') }}" class="hover:text-blue-700">Grados</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-graduation-cap mr-2 text-blue-600"></i>Crear Grado
        </h2>
        <p class="text-gray-500 text-sm mt-1">El nombre y el orden deben ser únicos dentro del nivel seleccionado.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @if($niveles->isEmpty())
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-5 text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No hay niveles activos registrados.
                <a href="{{ route('niveles.create') }}" class="font-semibold underline">Crea un nivel primero</a>.
            </div>
        @endif

        @include('grados._form', ['grado' => null])
    </div>
</div>
@endsection
