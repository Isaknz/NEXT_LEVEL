@extends('layouts.app')
@section('title', 'Cuentas por Cobrar - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cuentas-por-cobrar.index') }}" class="hover:text-blue-700">Cuentas por Cobrar</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Cuentas por Cobrar</h2>
    <p class="text-gray-500 mt-1">Administra las cuentas pendientes de cobro.</p>
</div>
<div class="bg-white rounded-xl shadow p-6">
    <p class="text-gray-500">Contenido del módulo. Lista, tabla o formulario aquí.</p>
</div>
@endsection