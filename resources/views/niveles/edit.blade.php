@extends('layouts.app')
@section('title', 'Editar Nivel - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('niveles.index') }}" class="hover:text-blue-700">Niveles</a></li>
    <li class="breadcrumb-separator">Editar</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-layer-group mr-2 text-blue-600"></i>Editar Nivel
        </h2>
        <p class="text-gray-500 text-sm mt-1">{{ $nivel->nombre }} · {{ $nivel->grados_count }} grado(s) asociado(s)</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('niveles._form', ['nivel' => $nivel])
    </div>
</div>
@endsection
