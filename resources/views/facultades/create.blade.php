@extends('layouts.app')
@section('title', 'Crear Facultad - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('facultades.index') }}" class="hover:text-blue-700">Facultades</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-university mr-2 text-blue-600"></i>Crear Facultad
        </h2>
        <p class="text-gray-500 text-sm mt-1">El nombre de la facultad debe ser único.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('facultades._form', ['facultad' => null])
    </div>
</div>
@endsection
