@extends('layouts.app')

@section('title', 'Nueva Matrícula - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>Registrar Nueva Matrícula
    </h2>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('matriculas.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Código -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-barcode text-indigo-500 mr-1"></i>Código de Matrícula *
                </label>
                <input type="text" name="codigo" value="{{ old('codigo', 'M' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Puedes modificarlo si es necesario</p>
            </div>

            <!-- Fecha -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar text-indigo-500 mr-1"></i>Fecha de Matrícula *
                </label>
                <input type="date" name="fecha_matricula" value="{{ old('fecha_matricula', date('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Alumno -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-graduate text-indigo-500 mr-1"></i>Alumno *
                </label>
                <select name="id_alumno" id="id_alumno" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar alumno</option>
                    @foreach($alumnos as $alumno)
                        <option value="{{ $alumno->id_alumno }}"
                                data-codigo="{{ $alumno->codigo }}"
                                data-modalidad="{{ str_starts_with($alumno->codigo ?? '', 'AC') ? 'ACADEMIA' : 'ESCOLAR' }}"
                                {{ old('id_alumno') == $alumno->id_alumno ? 'selected' : '' }}>
                            {{ $alumno->apellidos }}, {{ $alumno->nombres }} - DNI: {{ $alumno->dni ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Periodo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i>Periodo Académico *
                </label>
                <select name="id_periodo" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar periodo</option>
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" {{ old('id_periodo') == $periodo->id_periodo ? 'selected' : '' }}>
                            {{ $periodo->nombre }} ({{ $periodo->anio }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nivel -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-layer-group text-indigo-500 mr-1"></i>Nivel *
                </label>
                <select name="id_nivel" id="id_nivel" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar nivel</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id_nivel }}" {{ old('id_nivel') == $nivel->id_nivel ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Modalidad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tag text-indigo-500 mr-1"></i>Modalidad *
                </label>
                <select name="modalidad" id="modalidad" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar modalidad</option>
                    <option value="ESCOLAR" {{ old('modalidad') === 'ESCOLAR' ? 'selected' : '' }}>Escolar</option>
                    <option value="ACADEMIA" {{ old('modalidad') === 'ACADEMIA' ? 'selected' : '' }}>Academia</option>
                </select>
            </div>

            <!-- Tipo de Matrícula -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-list text-indigo-500 mr-1"></i>Tipo de Matrícula *
                </label>
                <select name="tipo_matricula" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar tipo</option>
                    <option value="NUEVO" {{ old('tipo_matricula') === 'NUEVO' ? 'selected' : '' }}>Nuevo</option>
                    <option value="REGULAR" {{ old('tipo_matricula') === 'REGULAR' ? 'selected' : '' }}>Regular</option>
                    <option value="TRASLADO" {{ old('tipo_matricula') === 'TRASLADO' ? 'selected' : '' }}>Traslado</option>
                    <option value="REINGRESO" {{ old('tipo_matricula') === 'REINGRESO' ? 'selected' : '' }}>Reingreso</option>
                </select>
            </div>

            <!-- Grado (Escolar) -->
            <div id="campo-grado">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-graduation-cap text-indigo-500 mr-1"></i>Grado (Escolar)
                </label>
                <select name="id_grado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar grado</option>
                    @foreach($grados as $grado)
                        <option value="{{ $grado->id_grado }}" {{ old('id_grado') == $grado->id_grado ? 'selected' : '' }}>
                            {{ $grado->nombre }} - {{ $grado->nivel->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Ciclo (Academia) -->
            <div id="campo-ciclo" class="hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-university text-purple-500 mr-1"></i>Ciclo (Academia)
                </label>
                <select name="id_ciclo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar ciclo</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id_ciclo }}" {{ old('id_ciclo') == $ciclo->id_ciclo ? 'selected' : '' }}>
                            {{ $ciclo->nombre }} - {{ $ciclo->facultad->nombre ?? '' }} ({{ $ciclo->turno }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-toggle-on text-indigo-500 mr-1"></i>Estado *
                </label>
                <select name="estado" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="PENDIENTE" {{ old('estado') === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                    <option value="ACTIVA" {{ old('estado', 'ACTIVA') === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                </select>
            </div>

            <!-- Observaciones -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-comment text-indigo-500 mr-1"></i>Observaciones
                </label>
                <textarea name="observaciones" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
            <a href="{{ route('matriculas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Guardar Matrícula
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalidadSelect = document.getElementById('modalidad');
        const campoGrado = document.getElementById('campo-grado');
        const campoCiclo = document.getElementById('campo-ciclo');
        const alumnoSelect = document.getElementById('id_alumno');

        function toggleModalidad() {
            const modalidad = modalidadSelect.value;

            if (modalidad === 'ESCOLAR') {
                campoGrado.classList.remove('hidden');
                campoCiclo.classList.add('hidden');
            } else if (modalidad === 'ACADEMIA') {
                campoGrado.classList.add('hidden');
                campoCiclo.classList.remove('hidden');
            } else {
                campoGrado.classList.remove('hidden');
                campoCiclo.classList.add('hidden');
            }
        }

        alumnoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const modalidad = selectedOption.getAttribute('data-modalidad');

            if (modalidad) {
                modalidadSelect.value = modalidad;
                toggleModalidad();
            }
        });

        modalidadSelect.addEventListener('change', toggleModalidad);
        toggleModalidad();
    });
</script>
@endsection
