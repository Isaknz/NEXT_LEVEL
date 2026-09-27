@extends('layouts.app')
@section('title', 'Editar Caja - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cajas.index') }}" class="hover:text-blue-700">Cajas</a></li>
    <li class="breadcrumb-separator">Editar</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-cash-register mr-2 text-blue-600"></i>Editar Caja
        </h2>
        <p class="text-gray-500 text-sm mt-1">
            {{ $caja->codigo }} · {{ $caja->movimientos_count }} movimiento(s) · {{ $caja->pagos_count }} pago(s) · {{ $caja->gastos_count }} gasto(s)
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @include('cajas._form', ['caja' => $caja])
    </div>
</div>
@endsection
