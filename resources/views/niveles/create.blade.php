@extends('layouts.app')
@section('title', 'Crear Nivel - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('niveles.index') }}" class="hover:text-blue-700">Niveles</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-layer-group mr-2 text-blue-600"></i>Crear Nivel
        </h2>
        <p class="text-gray-500 text-sm mt-1">Define un nivel educativo. El código y el nombre deben ser únicos.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('niveles._form', ['nivel' => null])
    </div>
</div>
@endsection
