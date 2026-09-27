@php
    $editing = isset($periodo) && $periodo;
    $anioActual = (int) date('Y');
@endphp

<form method="POST" action="{{ $editing ? route('periodos.update', $periodo) : route('periodos.store') }}" class="space-y-5">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="codigo" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-barcode mr-1 text-blue-600"></i>Código
            </label>
            <input type="text" name="codigo" id="codigo" maxlength="30" required
                   value="{{ old('codigo', $editing ? $periodo->codigo : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo') border-red-500 @enderror">
            @error('codigo')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="anio" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-calendar mr-1 text-blue-600"></i>Año
            </label>
            <input type="number" name="anio" id="anio" min="2000" max="2100" required
                   value="{{ old('anio', $editing ? $periodo->anio : $anioActual) }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('anio') border-red-500 @enderror">
            @error('anio')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-tag mr-1 text-blue-600"></i>Nombre
        </label>
        <input type="text" name="nombre" id="nombre" maxlength="100" required
               value="{{ old('nombre', $editing ? $periodo->nombre : '') }}"
               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
        @error('nombre')
            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="fecha_inicio" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-calendar-day mr-1 text-blue-600"></i>Fecha de Inicio
            </label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" required
                   value="{{ old('fecha_inicio', $editing ? $periodo->fecha_inicio?->format('Y-m-d') : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_inicio') border-red-500 @enderror">
            @error('fecha_inicio')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="fecha_fin" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-calendar-check mr-1 text-blue-600"></i>Fecha de Fin
            </label>
            <input type="date" name="fecha_fin" id="fecha_fin" required
                   value="{{ old('fecha_fin', $editing ? $periodo->fecha_fin?->format('Y-m-d') : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_fin') border-red-500 @enderror">
            @error('fecha_fin')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="estado" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-toggle-on mr-1 text-blue-600"></i>Estado
        </label>
        <select name="estado" id="estado" required
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('estado') border-red-500 @enderror">
            @foreach(['PLANIFICADO' => 'Planificado', 'ABIERTO' => 'Abierto', 'CERRADO' => 'Cerrado'] as $valor => $etiqueta)
                <option value="{{ $valor }}" {{ old('estado', $editing ? $periodo->estado : 'PLANIFICADO') === $valor ? 'selected' : '' }}>
                    {{ $etiqueta }}
                </option>
            @endforeach
        </select>
        @error('estado')
            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Guardar Cambios' : 'Crear Periodo' }}
        </button>
        <a href="{{ route('periodos.index') }}" class="btn-secondary px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>
