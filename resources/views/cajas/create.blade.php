@extends('layouts.app')
@section('title', 'Crear Caja - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cajas.index') }}" class="hover:text-blue-700">Cajas</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-cash-register mr-2 text-blue-600"></i>Crear Caja
        </h2>
        <p class="text-gray-500 text-sm mt-1">La caja se crea en estado ACTIVA. El código debe ser único.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('cajas._form', ['caja' => null])
    </div>
</div>
@endsection
