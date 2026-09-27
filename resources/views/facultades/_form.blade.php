@php
    $editing = isset($facultad) && $facultad;
@endphp

<form method="POST" action="{{ $editing ? route('facultades.update', $facultad) : route('facultades.store') }}" class="space-y-5">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div>
        <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-university mr-1 text-blue-600"></i>Nombre
        </label>
        <input type="text" name="nombre" id="nombre" maxlength="120" required
               value="{{ old('nombre', $editing ? $facultad->nombre : '') }}"
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
                <option value="{{ $valor }}" {{ old('estado', $editing ? $facultad->estado : 'ACTIVO') === $valor ? 'selected' : '' }}>
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
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Guardar Cambios' : 'Crear Facultad' }}
        </button>
        <a href="{{ route('facultades.index') }}" class="btn-secondary px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>
