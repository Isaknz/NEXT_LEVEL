@php
    $editing = isset($grado) && $grado;
@endphp

<form method="POST" action="{{ $editing ? route('grados.update', $grado) : route('grados.store') }}" class="space-y-5">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="id_nivel" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-layer-group mr-1 text-blue-600"></i>Nivel
            </label>
            <select name="id_nivel" id="id_nivel" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('id_nivel') border-red-500 @enderror">
                <option value="">Seleccione un nivel...</option>
                @foreach($niveles as $item)
                    <option value="{{ $item->id_nivel }}" {{ (int) old('id_nivel', $editing ? $grado->id_nivel : 0) === (int) $item->id_nivel ? 'selected' : '' }}>
                        {{ $item->codigo }} - {{ $item->nombre }}
                    </option>
                @endforeach
            </select>
            @error('id_nivel')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="orden" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-sort-numeric-up mr-1 text-blue-600"></i>Orden
            </label>
            <input type="number" name="orden" id="orden" min="1" max="255" required
                   value="{{ old('orden', $editing ? $grado->orden : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('orden') border-red-500 @enderror">
            @error('orden')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-graduation-cap mr-1 text-blue-600"></i>Nombre
            </label>
            <input type="text" name="nombre" id="nombre" maxlength="30" required
                   value="{{ old('nombre', $editing ? $grado->nombre : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
            @error('nombre')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="estado" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-toggle-on mr-1 text-blue-600"></i>Estado
            </label>
            <select name="estado" id="estado" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('estado') border-red-500 @enderror">
                @foreach(['ACTIVO' => 'Activo', 'INACTIVO' => 'Inactivo'] as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ old('estado', $editing ? $grado->estado : 'ACTIVO') === $valor ? 'selected' : '' }}>
                        {{ $etiqueta }}
                    </option>
                @endforeach
            </select>
            @error('estado')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Guardar Cambios' : 'Crear Grado' }}
        </button>
        <a href="{{ route('grados.index') }}" class="btn-secondary px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>
