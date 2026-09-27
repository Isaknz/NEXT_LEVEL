@extends('layouts.app')

@section('title', 'Editar Matrícula - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-edit mr-2 text-yellow-600"></i>Editar Matrícula: {{ $matricula->codigo }}
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

    <form action="{{ route('matriculas.update', $matricula->id_matricula) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Código -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo', $matricula->codigo) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Fecha -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Matrícula *</label>
                <input type="date" name="fecha_matricula"
                       value="{{ old('fecha_matricula', $matricula->fecha_matricula ? $matricula->fecha_matricula->format('Y-m-d') : date('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Alumno -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Alumno *</label>
                <select name="id_alumno" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar alumno</option>
                    @foreach($alumnos as $alumno)
                        <option value="{{ $alumno->id_alumno }}" {{ old('id_alumno', $matricula->id_alumno) == $alumno->id_alumno ? 'selected' : '' }}>
                            {{ $alumno->apellidos }}, {{ $alumno->nombres }} - DNI: {{ $alumno->dni ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Periodo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Periodo Académico *</label>
                <select name="id_periodo" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}" {{ old('id_periodo', $matricula->id_periodo) == $periodo->id_periodo ? 'selected' : '' }}>
                            {{ $periodo->nombre }} ({{ $periodo->anio }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nivel -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nivel *</label>
                <select name="id_nivel" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id_nivel }}" {{ old('id_nivel', $matricula->id_nivel) == $nivel->id_nivel ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Modalidad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Modalidad *</label>
                <select name="modalidad" id="modalidad" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="ESCOLAR" {{ old('modalidad', $matricula->modalidad) === 'ESCOLAR' ? 'selected' : '' }}>Escolar</option>
                    <option value="ACADEMIA" {{ old('modalidad', $matricula->modalidad) === 'ACADEMIA' ? 'selected' : '' }}>Academia</option>
                </select>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Matrícula *</label>
                <select name="tipo_matricula" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="NUEVO" {{ old('tipo_matricula', $matricula->tipo_matricula) === 'NUEVO' ? 'selected' : '' }}>Nuevo</option>
                    <option value="REGULAR" {{ old('tipo_matricula', $matricula->tipo_matricula) === 'REGULAR' ? 'selected' : '' }}>Regular</option>
                    <option value="TRASLADO" {{ old('tipo_matricula', $matricula->tipo_matricula) === 'TRASLADO' ? 'selected' : '' }}>Traslado</option>
                    <option value="REINGRESO" {{ old('tipo_matricula', $matricula->tipo_matricula) === 'REINGRESO' ? 'selected' : '' }}>Reingreso</option>
                </select>
            </div>

            <!-- Grado -->
            <div id="campo-grado">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Grado (Escolar)</label>
                <select name="id_grado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar grado</option>
                    @foreach($grados as $grado)
                        <option value="{{ $grado->id_grado }}" {{ old('id_grado', $matricula->id_grado) == $grado->id_grado ? 'selected' : '' }}>
                            {{ $grado->nombre }} - {{ $grado->nivel->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Ciclo -->
            <div id="campo-ciclo" class="hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Ciclo (Academia)</label>
                <select name="id_ciclo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Seleccionar ciclo</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id_ciclo }}" {{ old('id_ciclo', $matricula->id_ciclo) == $ciclo->id_ciclo ? 'selected' : '' }}>
                            {{ $ciclo->nombre }} - {{ $ciclo->facultad->nombre ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Estado *</label>
                <select name="estado" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="PENDIENTE" {{ old('estado', $matricula->estado) === 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                    <option value="ACTIVA" {{ old('estado', $matricula->estado) === 'ACTIVA' ? 'selected' : '' }}>Activa</option>
                    <option value="RETIRADA" {{ old('estado', $matricula->estado) === 'RETIRADA' ? 'selected' : '' }}>Retirada</option>
                    <option value="ANULADA" {{ old('estado', $matricula->estado) === 'ANULADA' ? 'selected' : '' }}>Anulada</option>
                    <option value="FINALIZADA" {{ old('estado', $matricula->estado) === 'FINALIZADA' ? 'selected' : '' }}>Finalizada</option>
                    {{-- PAGADA la calcula el sistema al liquidar la deuda; sin esta
                         opción el navegador seleccionaba "Pendiente" y se perdía. --}}
                    <option value="PAGADA" {{ old('estado', $matricula->estado) === 'PAGADA' ? 'selected' : '' }}>Pagada (automático)</option>
                </select>
                @if ($matricula->estado === 'PAGADA')
                    <p class="mt-1 text-xs text-gray-500">El estado Pagada se asigna automáticamente cuando la matrícula queda sin saldo pendiente.</p>
                @endif
            </div>

            <!-- Observaciones -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Observaciones</label>
                <textarea name="observaciones" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('observaciones', $matricula->observaciones) }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
            <a href="{{ route('matriculas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Actualizar Matrícula
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalidadSelect = document.getElementById('modalidad');
        const campoGrado = document.getElementById('campo-grado');
        const campoCiclo = document.getElementById('campo-ciclo');

        function toggleModalidad() {
            if (modalidadSelect.value === 'ACADEMIA') {
                campoGrado.classList.add('hidden');
                campoCiclo.classList.remove('hidden');
            } else {
                campoGrado.classList.remove('hidden');
                campoCiclo.classList.add('hidden');
            }
        }

        modalidadSelect.addEventListener('change', toggleModalidad);
        toggleModalidad();
    });
</script>
@endsection
