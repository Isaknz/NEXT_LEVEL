@extends('layouts.app')
@section('title', 'Crear Ciclo - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('ciclos.index') }}" class="hover:text-blue-700">Ciclos</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-book-open mr-2 text-blue-600"></i>Crear Ciclo Académico
        </h2>
        <p class="text-gray-500 text-sm mt-1">El nombre y turno deben ser únicos para cada combinación de período y facultad.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @if($periodos->isEmpty() || $facultades->isEmpty())
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-5 text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                @if($periodos->isEmpty())
                    No hay períodos disponibles.
                    <a href="{{ route('periodos.create') }}" class="font-semibold underline">Crea un período</a>.
                @endif
                @if($facultades->isEmpty())
                    No hay facultades activas.
                    <a href="{{ route('facultades.create') }}" class="font-semibold underline">Crea una facultad</a>.
                @endif
            </div>
        @endif

        @include('ciclos._form', ['ciclo' => null])
    </div>
</div>
@endsection
