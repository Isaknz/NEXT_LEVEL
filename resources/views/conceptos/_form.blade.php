@php
    $editing = isset($concepto) && $concepto;
@endphp

<form method="POST" action="{{ $editing ? route('conceptos.update', $concepto) : route('conceptos.store') }}" class="space-y-5">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="codigo" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-barcode mr-1 text-blue-600"></i>Código
            </label>
            <input type="text" name="codigo" id="codigo" maxlength="40" required
                   value="{{ old('codigo', $editing ? $concepto->codigo : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo') border-red-500 @enderror">
            @error('codigo')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="tipo" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-sitemap mr-1 text-blue-600"></i>Tipo
            </label>
            <select name="tipo" id="tipo" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('tipo') border-red-500 @enderror">
                @foreach([
                    'MATRICULA' => 'Matrícula',
                    'MENSUALIDAD' => 'Mensualidad',
                    'CICLO' => 'Ciclo',
                    'MATERIAL' => 'Material',
                    'EXAMEN' => 'Examen',
                    'OTRO' => 'Otro',
                ] as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ old('tipo', $editing ? $concepto->tipo : 'MATRICULA') === $valor ? 'selected' : '' }}>
                        {{ $etiqueta }}
                    </option>
                @endforeach
            </select>
            @error('tipo')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-file-invoice-dollar mr-1 text-blue-600"></i>Nombre
        </label>
        <input type="text" name="nombre" id="nombre" maxlength="120" required
               value="{{ old('nombre', $editing ? $concepto->nombre : '') }}"
               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
        @error('nombre')
            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="modalidad_aplicable" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-user-graduate mr-1 text-blue-600"></i>Modalidad Aplicable
            </label>
            <select name="modalidad_aplicable" id="modalidad_aplicable" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('modalidad_aplicable') border-red-500 @enderror">
                @foreach([
                    'ESCOLAR' => 'Escolar',
                    'ACADEMIA' => 'Academia',
                    'AMBOS' => 'Ambos',
                ] as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ old('modalidad_aplicable', $editing ? $concepto->modalidad_aplicable : 'AMBOS') === $valor ? 'selected' : '' }}>
                        {{ $etiqueta }}
                    </option>
                @endforeach
            </select>
            @error('modalidad_aplicable')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="monto_referencial" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-money-bill-wave mr-1 text-blue-600"></i>Monto Referencial
            </label>
            <input type="number" name="monto_referencial" id="monto_referencial" step="0.01" min="0" max="99999999.99" required
                   value="{{ old('monto_referencial', $editing ? $concepto->monto_referencial : '0.00') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto_referencial') border-red-500 @enderror">
            @error('monto_referencial')
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
            @foreach(['ACTIVO' => 'Activo', 'INACTIVO' => 'Inactivo'] as $valor => $etiqueta)
                <option value="{{ $valor }}" {{ old('estado', $editing ? $concepto->estado : 'ACTIVO') === $valor ? 'selected' : '' }}>
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
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Guardar Cambios' : 'Crear Concepto' }}
        </button>
        <a href="{{ route('conceptos.index') }}" class="btn-secondary px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>
