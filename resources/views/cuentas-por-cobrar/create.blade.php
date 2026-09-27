@extends('layouts.app')
@section('title', 'Crear Cuenta por Cobrar - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cuentas-por-cobrar.index') }}" class="hover:text-blue-700">Cuentas por Cobrar</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>Crear Cuenta por Cobrar
        </h2>
        <p class="text-gray-500 text-sm mt-1">Genera una deuda asociada a una matrícula y un concepto de cobro.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        @if($matriculas->isEmpty() || $conceptos->isEmpty())
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-5 text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                @if($matriculas->isEmpty())
                    No hay matrículas disponibles. <a href="{{ route('matriculas.create') }}" class="font-semibold underline">Crea una matrícula</a>.
                @endif
                @if($conceptos->isEmpty())
                    No hay conceptos activos. <a href="{{ route('conceptos.create') }}" class="font-semibold underline">Crea un concepto</a>.
                @endif
            </div>
        @endif

        @include('cuentas-por-cobrar._form', ['cuenta' => null])
    </div>
</div>
@endsection
