@extends('layouts.app')
@section('title', 'Categorías de Gasto - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('categorias.index') }}" class="hover:text-blue-700">Categorías</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Categorías de Gasto</h2>
    <p class="text-gray-500 mt-1">Administra las categorías de gasto del sistema.</p>
</div>
<div class="bg-white rounded-xl shadow p-6">
    <p class="text-gray-500">Contenido del módulo. Lista, tabla o formulario aquí.</p>
</div>
@endsection