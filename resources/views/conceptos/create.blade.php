@extends('layouts.app')
@section('title', 'Crear Concepto - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('conceptos.index') }}" class="hover:text-blue-700">Conceptos</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-file-invoice-dollar mr-2 text-blue-600"></i>Crear Concepto de Cobro
        </h2>
        <p class="text-gray-500 text-sm mt-1">El código debe ser único. El monto referencial es solo informativo.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('conceptos._form', ['concepto' => null])
    </div>
</div>
@endsection
