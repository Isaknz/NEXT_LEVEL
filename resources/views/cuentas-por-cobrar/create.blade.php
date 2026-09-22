@extends('layouts.app')
@section('title', 'Crear Cuenta - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cuentas-por-cobrar.index') }}" class="hover:text-blue-700">Cuentas</a></li>
    <li class="breadcrumb-separator">Crear</li>
@endsection
@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Crear Cuenta</h2>
    <form method="POST" action="{{ route('cuentas-por-cobrar.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">ID de Matrícula</label>
            <input type="text" name="id_matricula" value="{{ old('id_matricula', $cuenta->id_matricula ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('id_matricula')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">ID de Concepto</label>
            <input type="text" name="id_concepto" value="{{ old('id_concepto', $cuenta->id_concepto ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('id_concepto')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de Vencimiento</label>
            <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $cuenta->fecha_vencimiento ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('fecha_vencimiento')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Monto Original</label>
            <input type="text" name="monto_original" value="{{ old('monto_original', $cuenta->monto_original ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('monto_original')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Monto Pagado</label>
            <input type="text" name="monto_pagado" value="{{ old('monto_pagado', $cuenta->monto_pagado ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('monto_pagado')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
            <input type="text" name="estado" value="{{ old('estado', $cuenta->estado ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('estado')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Período</label>
            <input type="text" name="periodo" value="{{ old('periodo', $cuenta->periodo ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            @error('periodo')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Observaciones</label>
            <input type="text" name="observaciones" value="{{ old('observaciones', $cuenta->observaciones ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('observaciones')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary rounded-lg px-4 py-2 font-semibold text-white">
            <i class="fas fa-save"></i> Guardar
        </button>
    </form>
</div>
@endsection