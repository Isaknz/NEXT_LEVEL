@extends('layouts.app')
@section('title', 'Editar Ciclo - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('ciclos.index') }}" class="hover:text-blue-700">Ciclos</a></li>
    <li class="breadcrumb-separator">Editar</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-book-open mr-2 text-blue-600"></i>Editar Ciclo Académico
        </h2>
        <p class="text-gray-500 text-sm mt-1">
            {{ $ciclo->nombre }} · {{ $ciclo->matriculas_count }} matrícula(s)
            @if($ciclo->periodo) · {{ $ciclo->periodo->codigo }} @endif
            @if($ciclo->facultad) · {{ $ciclo->facultad->nombre }} @endif
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('ciclos._form', ['ciclo' => $ciclo])
    </div>
</div>
@endsection
