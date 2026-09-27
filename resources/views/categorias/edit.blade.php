@extends('layouts.app')
@section('title', 'Editar Categoría - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('categorias.index') }}" class="hover:text-blue-700">Categorías</a></li>
    <li class="breadcrumb-separator">Editar</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-tags mr-2 text-blue-600"></i>Editar Categoría de Gasto
        </h2>
        <p class="text-gray-500 text-sm mt-1">{{ $categoria->nombre }} · {{ $categoria->gastos_count }} gasto(s) asociado(s)</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('categorias._form', ['categoria' => $categoria])
    </div>
</div>
@endsection
