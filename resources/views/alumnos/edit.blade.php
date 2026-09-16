@extends('layouts.app')

@section('title', 'Editar Alumno - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Encabezado -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-edit mr-2 text-yellow-600"></i>Editar Alumno
        </h2>
        <p class="text-gray-500 text-sm mt-1">Modifica la información del alumno</p>
    </div>

    <!-- Alertas de error -->
    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow" id="alerta-error">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                    <div>
                        <p class="font-bold">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button onclick="document.getElementById('alerta-error').remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('alumnos.update', $alumno->id_alumno) }}" method="POST" class="bg-white rounded-xl shadow-md overflow-hidden">
        @csrf
        @method('PUT')

        <!-- Encabezado del formulario -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-4">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</h3>
                    <p class="text-sm text-blue-100">Código: {{ $alumno->codigo ?? 'N/A' }} | DNI: {{ $alumno->dni ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-barcode text-blue-500 mr-1"></i>Código
                    </label>
                    <input type="text" name="codigo" value="{{ old('codigo', $alumno->codigo) }}"
                           placeholder="Ej: A001"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- DNI -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-id-card text-blue-500 mr-1"></i>DNI
                    </label>
                    <input type="text" name="dni" value="{{ old('dni', $alumno->dni) }}" maxlength="8"
                           placeholder="8 dígitos"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Nombres -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user text-blue-500 mr-1"></i>Nombres *
                    </label>
                    <input type="text" name="nombres" value="{{ old('nombres', $alumno->nombres) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Apellidos -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-users text-blue-500 mr-1"></i>Apellidos *
                    </label>
                    <input type="text" name="apellidos" value="{{ old('apellidos', $alumno->apellidos) }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Fecha de Nacimiento -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar text-blue-500 mr-1"></i>Fecha de Nacimiento
                    </label>
                    <input type="date" name="fecha_nacimiento"
                           value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ? $alumno->fecha_nacimiento->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Sexo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-venus-mars text-blue-500 mr-1"></i>Sexo
                    </label>
                    <select name="sexo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white transition">
                        <option value="">Seleccionar</option>
                        <option value="F" {{ old('sexo', $alumno->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="M" {{ old('sexo', $alumno->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="OTRO" {{ old('sexo', $alumno->sexo) === 'OTRO' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <!-- Grado -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap text-blue-500 mr-1"></i>Grado
                    </label>
                    <select name="id_grado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white transition">
                        <option value="">Seleccionar grado</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id_grado }}" {{ old('id_grado', $alumno->id_grado) == $grado->id_grado ? 'selected' : '' }}>
                                {{ $grado->nombre }} - {{ $grado->nivel->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Celular -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone text-blue-500 mr-1"></i>Celular
                    </label>
                    <input type="text" name="celular" value="{{ old('celular', $alumno->celular) }}"
                           placeholder="999888777"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope text-blue-500 mr-1"></i>Email
                    </label>
                    <input type="email" name="email" value="{{ old('email', $alumno->email) }}"
                           placeholder="correo@ejemplo.com"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>Dirección
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion', $alumno->direccion) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-toggle-on text-blue-500 mr-1"></i>Estado
                    </label>
                    <select name="estado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white transition">
                        <option value="ACTIVO" {{ old('estado', $alumno->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVO" {{ old('estado', $alumno->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-8 flex justify-end gap-3 border-t border-gray-200 pt-6">
                <a href="{{ route('alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow transition">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                    <i class="fas fa-save mr-1"></i> Actualizar Alumno
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
