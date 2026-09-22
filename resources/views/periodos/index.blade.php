@extends('layouts.app')
@section('title', 'Periodos Académicos - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('periodos.index') }}" class="hover:text-blue-700">Periodos</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Periodos Académicos</h2>
    <p class="text-gray-500 mt-1">Administra los periodos académicos del sistema.</p>
</div>
<div class="bg-white rounded-xl shadow p-6">
    <p class="text-gray-500">Contenido del módulo. Lista, tabla o formulario aquí.</p>
</div>
@endsection