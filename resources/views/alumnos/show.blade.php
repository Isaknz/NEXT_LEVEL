@extends('layouts.app')

@section('title', 'Detalle Alumno - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Encabezado -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user mr-2 text-blue-600"></i>Detalle del Alumno
            </h2>
            <p class="text-gray-500 text-sm mt-1">Información completa del alumno</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('alumnos.edit', $alumno->id_alumno) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm shadow transition">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <a href="{{ route('alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Tarjeta principal -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Encabezado con gradiente -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    @if($alumno->es_academia)
                        <i class="fas fa-university text-white text-2xl"></i>
                    @else
                        <i class="fas fa-user text-white text-2xl"></i>
                    @endif
                </div>
                <div>
                    <h3 class="text-2xl font-bold">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</h3>
                    <p class="text-blue-100 mt-1">
                        Código: {{ $alumno->codigo ?? 'N/A' }}
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        @if($alumno->estado === 'ACTIVO')
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1"></i>ACTIVO
                            </span>
                        @else
                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-circle mr-1"></i>INACTIVO
                            </span>
                        @endif

                        @if($alumno->es_academia)
                            <span class="bg-purple-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                <i class="fas fa-university mr-1"></i>ACADEMIA
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido -->
        <div class="p-6">
            <!-- Información en 2 columnas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Información Personal -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-id-card text-blue-600 mr-2"></i>Información Personal
                    </h4>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-id-card text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">DNI</p>
                                <p class="font-semibold text-gray-800">{{ $alumno->dni ?? 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-calendar text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Fecha de Nacimiento</p>
                                <p class="font-semibold text-gray-800">{{ $alumno->fecha_nacimiento ? $alumno->fecha_nacimiento->format('d/m/Y') : 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-venus-mars text-pink-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Sexo</p>
                                <p class="font-semibold text-gray-800">
                                    @if($alumno->sexo === 'F')
                                        Femenino
                                    @elseif($alumno->sexo === 'M')
                                        Masculino
                                    @else
                                        {{ $alumno->sexo ?? 'No registrado' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-graduation-cap text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Grado / Nivel</p>
                                @if($alumno->grado)
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        {{ $alumno->grado->nombre }} - {{ $alumno->grado->nivel->nombre }}
                                    </span>
                                @elseif($alumno->es_academia)
                                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-university mr-1"></i>Academia
                                    </span>
                                @else
                                    <p class="text-gray-400">Sin asignar</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-address-book text-green-600 mr-2"></i>Contacto
                    </h4>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-phone text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Celular</p>
                                <p class="font-semibold text-gray-800">{{ $alumno->celular ?? 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-envelope text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-semibold text-gray-800">{{ $alumno->email ?? 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-map-marker-alt text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Dirección</p>
                                <p class="font-semibold text-gray-800">{{ $alumno->direccion ?? 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-users text-teal-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Apoderado</p>
                                <p class="font-semibold text-gray-800">{{ $alumno->apoderado->nombre_apod ?? 'No registrado' }}</p>
                                @if($alumno->parentesco)
                                    <span class="text-xs text-gray-500">Parentesco: {{ $alumno->parentesco }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matrículas -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-file-invoice text-indigo-600 mr-2"></i>Matrículas
                </h4>
                @if($alumno->matriculas && $alumno->matriculas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Periodo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nivel</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($alumno->matriculas as $matricula)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium">{{ $matricula->codigo }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $matricula->periodo->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $matricula->nivel->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">{{ $matricula->estado }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-file-invoice text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">No hay matrículas registradas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
