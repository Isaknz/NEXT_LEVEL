@extends('layouts.app')

@section('title', 'Nuevo Alumno - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-user-plus mr-2 text-blue-600"></i>Registrar Nuevo Alumno
    </h2>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
                <span class="font-bold">Por favor corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumnos.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Código</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: A001"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">DNI</label>
                <input type="text" name="dni" value="{{ old('dni') }}" maxlength="8" placeholder="8 dígitos"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nombres *</label>
                <input type="text" name="nombres" value="{{ old('nombres') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Apellidos *</label>
                <input type="text" name="apellidos" value="{{ old('apellidos') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sexo</label>
                <select name="sexo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Seleccionar</option>
                    <option value="F">Femenino</option>
                    <option value="M">Masculino</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Modalidad *</label>
                <select name="modalidad" id="modalidad" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="ESCOLAR" {{ old('modalidad', 'ESCOLAR') === 'ESCOLAR' ? 'selected' : '' }}>Escolar (Primaria / Secundaria)</option>
                    <option value="ACADEMIA" {{ old('modalidad') === 'ACADEMIA' ? 'selected' : '' }}>Academia</option>
                </select>
            </div>

            <div id="campo-grado">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Grado</label>
                <select name="id_grado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Seleccionar grado</option>
                    @foreach($grados as $grado)
                        <option value="{{ $grado->id_grado }}" {{ old('id_grado') == $grado->id_grado ? 'selected' : '' }}>
                            {{ $grado->nombre }} - {{ $grado->nivel->nombre }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1 hidden" id="nota-grado">Los alumnos de academia no tienen grado escolar; su nivel se define en la matrícula (ciclo).</p>
            </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Guardar Alumno
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalidad = document.getElementById('modalidad');
    const campoGrado = document.getElementById('campo-grado');
    const gradoSelect = campoGrado.querySelector('select');
    const notaGrado = document.getElementById('nota-grado');

    function toggle() {
        const esAcademia = modalidad.value === 'ACADEMIA';
        campoGrado.classList.toggle('hidden', esAcademia);
        notaGrado.classList.toggle('hidden', !esAcademia);
        if (esAcademia) {
            gradoSelect.value = '';
            gradoSelect.disabled = true;
        } else {
            gradoSelect.disabled = false;
        }
    }

    modalidad.addEventListener('change', toggle);
    toggle();
});
</script>
@endsection
