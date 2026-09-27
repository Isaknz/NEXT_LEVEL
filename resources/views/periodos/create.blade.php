@extends('layouts.app')
@section('title', 'Crear Periodo - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('periodos.index') }}" class="hover:text-blue-700">Periodos</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Crear Periodo Académico
        </h2>
        <p class="text-gray-500 text-sm mt-1">El código debe ser único y la fecha de fin no puede ser anterior al inicio.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('periodos._form', ['periodo' => null])
    </div>
</div>
@endsection
