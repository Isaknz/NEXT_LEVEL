@php
    $editing = isset($cuenta) && $cuenta;
@endphp

<form method="POST" action="{{ $editing ? route('cuentas-por-cobrar.update', $cuenta) : route('cuentas-por-cobrar.store') }}" class="space-y-5">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="id_matricula" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-user-graduate mr-1 text-blue-600"></i>Matrícula
            </label>
            <select name="id_matricula" id="id_matricula" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('id_matricula') border-red-500 @enderror">
                <option value="">Seleccione una matrícula...</option>
                @foreach($matriculas as $matricula)
                    <option value="{{ $matricula->id_matricula }}" {{ (int) old('id_matricula', $editing ? $cuenta->id_matricula : 0) === (int) $matricula->id_matricula ? 'selected' : '' }}>
                        {{ $matricula->codigo }} - {{ $matricula->alumno?->nombres }} {{ $matricula->alumno?->apellidos }}
                    </option>
                @endforeach
            </select>
            @error('id_matricula')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="id_concepto" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-file-invoice-dollar mr-1 text-blue-600"></i>Concepto
            </label>
            <select name="id_concepto" id="id_concepto" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white @error('id_concepto') border-red-500 @enderror">
                <option value="">Seleccione un concepto...</option>
                @foreach($conceptos as $concepto)
                    <option value="{{ $concepto->id_concepto }}" {{ (int) old('id_concepto', $editing ? $cuenta->id_concepto : 0) === (int) $concepto->id_concepto ? 'selected' : '' }}>
                        {{ $concepto->codigo }} - {{ $concepto->nombre }}
                    </option>
                @endforeach
            </select>
            @error('id_concepto')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="referencia" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-hashtag mr-1 text-blue-600"></i>Referencia
        </label>
        <input type="text" name="referencia" id="referencia" maxlength="80" required
               value="{{ old('referencia', $editing ? $cuenta->referencia : '') }}"
               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('referencia') border-red-500 @enderror">
        <p class="text-xs text-gray-500 mt-1">Debe ser única para la combinación de matrícula y concepto.</p>
        @error('referencia')
            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-1">
            <i class="fas fa-align-left mr-1 text-blue-600"></i>Descripción
        </label>
        <input type="text" name="descripcion" id="descripcion" maxlength="255"
               value="{{ old('descripcion', $editing ? $cuenta->descripcion : '') }}"
               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror">
        @error('descripcion')
            <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="fecha_emision" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-calendar-day mr-1 text-blue-600"></i>Fecha de Emisión
            </label>
            <input type="date" name="fecha_emision" id="fecha_emision" required
                   value="{{ old('fecha_emision', $editing ? $cuenta->fecha_emision?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_emision') border-red-500 @enderror">
            @error('fecha_emision')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="fecha_vencimiento" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-calendar-xmark mr-1 text-blue-600"></i>Fecha de Vencimiento
            </label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
                   value="{{ old('fecha_vencimiento', $editing ? $cuenta->fecha_vencimiento?->format('Y-m-d') : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_vencimiento') border-red-500 @enderror">
            @error('fecha_vencimiento')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
            <label for="monto_original" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-money-bill-wave mr-1 text-blue-600"></i>Monto Original
            </label>
            <input type="number" name="monto_original" id="monto_original" step="0.01" min="0.01" max="99999999.99" required
                   value="{{ old('monto_original', $editing ? $cuenta->monto_original : '') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto_original') border-red-500 @enderror">
            @error('monto_original')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="descuento" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-minus-circle mr-1 text-green-600"></i>Descuento
            </label>
            <input type="number" name="descuento" id="descuento" step="0.01" min="0" max="99999999.99"
                   value="{{ old('descuento', $editing ? $cuenta->descuento : '0.00') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descuento') border-red-500 @enderror">
            @error('descuento')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="recargo" class="block text-sm font-semibold text-gray-700 mb-1">
                <i class="fas fa-plus-circle mr-1 text-red-600"></i>Recargo
            </label>
            <input type="number" name="recargo" id="recargo" step="0.01" min="0" max="99999999.99"
                   value="{{ old('recargo', $editing ? $cuenta->recargo : '0.00') }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('recargo') border-red-500 @enderror">
            @error('recargo')
                <p class="text-red-500 text-sm mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
        <i class="fas fa-calculator mr-1"></i>
        Total a cobrar = monto original - descuento + recargo.
        La cuenta se crea en estado <strong>PENDIENTE</strong> y se actualiza sola a PARCIAL o PAGADA según los pagos aplicados.
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-save mr-1"></i> {{ $editing ? 'Guardar Cambios' : 'Crear Cuenta' }}
        </button>
        <a href="{{ route('cuentas-por-cobrar.index') }}" class="btn-secondary px-5 py-2.5 rounded-lg text-sm shadow">
            <i class="fas fa-times mr-1"></i> Cancelar
        </a>
    </div>
</form>
