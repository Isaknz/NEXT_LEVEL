@extends('layouts.app')
@section('title', 'Facultades/Áreas - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('facultades.index') }}" class="hover:text-blue-700">Facultades</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Facultades/Áreas</h2>
    <p class="text-gray-500 mt-1">Administra las facultades y áreas de la institución.</p>
</div>
<div class="bg-white rounded-xl shadow p-6">
    <p class="text-gray-500">Contenido del módulo. Lista, tabla o formulario aquí.</p>
</div>
@endsection