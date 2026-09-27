@php
    $editing = isset($caja) && $caja;
@endphp

<form method="POST" action="{{ $editing ? route('cajas.update', $caja) : route('cajas.store') }}" class="space-y-5">
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
                   value="{{ old('codigo', $editing ? $caja->codigo : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo') border-red-500 @enderror">
            @error('codigo')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-cash-register mr-1 text-blue-600"></i>Nombre
            </label>
            <input type="text" name="nombre" id="nombre" maxlength="100" required
                   value="{{ old('nombre', $editing ? $caja->nombre : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
            @error('nombre')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="tipo" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-money-bill mr-1 text-blue-600"></i>Tipo
            </label>
            <select name="tipo" id="tipo" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('tipo') border-red-500 @enderror">
                @foreach([
                    'EFECTIVO' => 'Efectivo',
                    'BANCO' => 'Banco',
                    'BILLETERA_DIGITAL' => 'Billetera digital',
                ] as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ old('tipo', $editing ? $caja->tipo : 'EFECTIVO') === $valor ? 'selected' : '' }}>
                        {{ $etiqueta }}
                    </option>
                @endforeach
            </select>
            @error('tipo')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="saldo_inicial" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-coins mr-1 text-blue-600"></i>Saldo Inicial
            </label>
            <input type="number" name="saldo_inicial" id="saldo_inicial" step="0.01" min="0" max="99999999.99"
                   value="{{ old('saldo_inicial', $editing ? $caja->saldo_inicial : '0.00') }}"
                   @disabled($editing && $caja->movimientos_count > 0)
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500 @error('saldo_inicial') border-red-500 @enderror">
            @if($editing && $caja->movimientos_count > 0)
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-lock mr-1"></i>No editable: la caja ya tiene movimientos registrados.
                </p>
            @endif
            @error('saldo_inicial')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="estado" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-toggle-on mr-1 text-blue-600"></i>Estado
        </label>
        <select name="estado" id="estado" required
                @disabled($editing && $caja->estado === 'CERRADA')
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white disabled:bg-gray-100 disabled:text-gray-500 @error('estado') border-red-500 @enderror">
            @foreach(['ACTIVA' => 'Activa', 'INACTIVA' => 'Inactiva'] as $valor => $etiqueta)
                <option value="{{ $valor }}" {{ old('estado', $editing ? $caja->estado : 'ACTIVA') === $valor ? 'selected' : '' }}>
                    {{ $etiqueta }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">
            <i class="fas fa-info-circle mr-1"></i>
            El estado CERRADA se asigna con la acción «Cerrar caja», no desde este formulario.
        </p>
        @error('estado')
            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Guardar Cambios' : 'Crear Caja' }}
        </button>
        <a href="{{ route('cajas.index') }}" class="btn-secondary px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>
