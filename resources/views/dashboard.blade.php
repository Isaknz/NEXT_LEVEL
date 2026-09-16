@extends('layouts.app')

@section('title', 'Dashboard - Next Level')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">
        ¡Bienvenido, {{ auth()->user()->nombre }}!
    </h2>
    <p class="text-gray-600 mt-2">Resumen general del sistema</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Alumnos</p>
                <p class="text-3xl font-bold text-gray-800">{{ \App\Models\Alumno::count() }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Apoderados</p>
                <p class="text-3xl font-bold text-gray-800">{{ \App\Models\Apoderado::count() }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Ingresos (Pagos)</p>
                <p class="text-3xl font-bold text-green-600">S/. {{ number_format(\App\Models\Pago::where('estado', 'CONFIRMADO')->sum('monto_total'), 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                <i class="fas fa-arrow-down text-emerald-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Gastos</p>
                <p class="text-3xl font-bold text-red-600">S/. {{ number_format(\App\Models\Gasto::where('estado', 'REGISTRADO')->sum('monto'), 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-arrow-up text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('alumnos.index') }}" class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <i class="fas fa-user-graduate text-2xl text-blue-600 mr-3"></i>
            <h3 class="text-lg font-semibold">Alumnos</h3>
        </div>
        <p class="text-gray-600 text-sm">Gestionar alumnos registrados</p>
    </a>

    <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <i class="fas fa-file-invoice text-2xl text-indigo-600 mr-3"></i>
            <h3 class="text-lg font-semibold">Matrículas</h3>
        </div>
        <p class="text-gray-600 text-sm">Gestionar matrículas</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <i class="fas fa-money-bill text-2xl text-green-600 mr-3"></i>
            <h3 class="text-lg font-semibold">Pagos</h3>
        </div>
        <p class="text-gray-600 text-sm">Registrar pagos de alumnos</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <i class="fas fa-receipt text-2xl text-red-600 mr-3"></i>
            <h3 class="text-lg font-semibold">Gastos</h3>
        </div>
        <p class="text-gray-600 text-sm">Registrar egresos del colegio</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-triangle text-2xl text-yellow-600 mr-3"></i>
            <h3 class="text-lg font-semibold">Deudores</h3>
        </div>
        <p class="text-gray-600 text-sm">Alumnos con pagos pendientes</p>
    </div>

    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
    <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
        <div class="flex items-center mb-4">
            <i class="fas fa-history text-2xl text-gray-600 mr-3"></i>
            <h3 class="text-lg font-semibold">Auditoría</h3>
        </div>
        <p class="text-gray-600 text-sm">Registro de movimientos</p>
    </div>
    @endif
</div>
@endsection
