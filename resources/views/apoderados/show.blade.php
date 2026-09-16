@extends('layouts.app')

@section('title', 'Detalle Apoderado - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-user mr-2 text-green-600"></i>Detalle del Apoderado
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('apoderados.edit', $apoderado->id_apoderado) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            <a href="{{ route('apoderados.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6">
            <div class="flex items-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold">{{ $apoderado->apellidos }}, {{ $apoderado->nombres }}</h3>
                    <p class="text-green-100 mt-1">DNI: {{ $apoderado->dni ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-phone text-green-600 w-6"></i>
                            <span>{{ $apoderado->celular ?? 'No registrado' }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-yellow-600 w-6"></i>
                            <span>{{ $apoderado->email ?? 'No registrado' }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-red-600 w-6"></i>
                            <span>{{ $apoderado->direccion ?? 'No registrado' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Estado</h4>
                    @if($apoderado->estado === 'ACTIVO')
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">ACTIVO</span>
                    @else
                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">INACTIVO</span>
                    @endif
                </div>
            </div>

            <div class="mt-8 border-t pt-6">
                <h4 class="text-lg font-semibold mb-4">
                    <i class="fas fa-child text-blue-600 mr-2"></i>Hijos / Alumnos a cargo
                </h4>
                @if($apoderado->alumnos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Alumno</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Grado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Parentesco</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($apoderado->alumnos as $alumno)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</td>
                                    <td class="px-4 py-3">
                                        @if($alumno->grado)
                                            {{ $alumno->grado->nombre }} {{ $alumno->grado->nivel->nombre }}
                                        @elseif($alumno->es_academia)
                                            <span class="text-purple-600">Academia</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $alumno->parentesco ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No tiene alumnos asignados</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
