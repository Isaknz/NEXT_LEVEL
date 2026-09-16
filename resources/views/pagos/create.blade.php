@extends('layouts.app')

@section('title', 'Registrar Pago - Next Level')

@section('content')
<div class="max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>Registrar Nuevo Pago
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

    <form action="{{ route('pagos.store') }}" method="POST" id="form-pago" class="bg-white rounded-xl shadow-md p-6">
        @csrf

        <!-- Datos generales -->
        <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">
            <i class="fas fa-info-circle text-green-600 mr-2"></i>Datos del Pago
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Código *</label>
                <input type="text" name="codigo" id="codigo"
                       value="{{ old('codigo', 'P' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Pago *</label>
                <input type="datetime-local" name="fecha_pago"
                       value="{{ old('fecha_pago', now()->format('Y-m-d\TH:i')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Caja *</label>
                <select name="id_caja" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                    <option value="">Seleccionar caja</option>
                    @foreach($cajas as $caja)
                        <option value="{{ $caja->id_caja }}" {{ old('id_caja') == $caja->id_caja ? 'selected' : '' }}>
                            {{ $caja->nombre }} ({{ $caja->tipo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Método de Pago *</label>
                <select name="metodo_pago" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                    <option value="">Seleccionar método</option>
                    <option value="EFECTIVO" {{ old('metodo_pago') === 'EFECTIVO' ? 'selected' : '' }}>Efectivo</option>
                    <option value="YAPE" {{ old('metodo_pago') === 'YAPE' ? 'selected' : '' }}>Yape</option>
                    <option value="PLIN" {{ old('metodo_pago') === 'PLIN' ? 'selected' : '' }}>Plin</option>
                    <option value="TRANSFERENCIA" {{ old('metodo_pago') === 'TRANSFERENCIA' ? 'selected' : '' }}>Transferencia</option>
                    <option value="TARJETA" {{ old('metodo_pago') === 'TARJETA' ? 'selected' : '' }}>Tarjeta</option>
                    <option value="OTRO" {{ old('metodo_pago') === 'OTRO' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Operación</label>
                <input type="text" name="numero_operacion" value="{{ old('numero_operacion') }}"
                       placeholder="Nro. de operación (opcional)"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        </div>

        <!-- Matrícula / Alumno -->
        <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">
            <i class="fas fa-user-graduate text-green-600 mr-2"></i>Alumno y Cuentas por Cobrar
        </h3>

        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Seleccionar Matrícula *</label>
            <select name="id_matricula" id="id_matricula" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                <option value="">Seleccionar matrícula...</option>
                @foreach($matriculas as $mat)
                    <option value="{{ $mat->id_matricula }}"
                            data-cuentas="{{ json_encode($mat->cuentasPorCobrar->map(function($c) {
                                return [
                                    'id_cuenta' => $c->id_cuenta,
                                    'concepto' => $c->concepto->nombre ?? 'N/A',
                                    'referencia' => $c->referencia,
                                    'monto_pendiente' => $c->monto_pendiente,
                                ];
                            })) }}"
                            {{ (old('id_matricula') == $mat->id_matricula || ($matriculaSeleccionada && $matriculaSeleccionada->id_matricula == $mat->id_matricula)) ? 'selected' : '' }}>
                        {{ $mat->alumno->apellidos ?? 'N/A' }}, {{ $mat->alumno->nombres ?? '' }} - {{ $mat->codigo }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Tabla de cuentas por cobrar -->
        <div id="cuentas-container" class="hidden">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Cuentas por cobrar pendientes:</h4>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Concepto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Referencia</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Pendiente</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Seleccionar</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Monto a Pagar</th>
                        </tr>
                    </thead>
                    <tbody id="cuentas-tbody" class="divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>
        </div>

        <div id="sin-cuentas" class="hidden text-center py-6 bg-gray-50 rounded-lg">
            <i class="fas fa-info-circle text-gray-400 text-3xl mb-2"></i>
            <p class="text-gray-500">Esta matrícula no tiene cuentas pendientes</p>
        </div>

        <!-- Observaciones -->
        <div class="mt-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Observaciones</label>
            <textarea name="observaciones" rows="2"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('observaciones') }}</textarea>
        </div>

        <!-- Total -->
        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-700">
                    <i class="fas fa-calculator text-green-600 mr-2"></i>Total a Pagar:
                </span>
                <span class="text-3xl font-bold text-green-600">
                    S/. <span id="total-monto">0.00</span>
                </span>
                <input type="hidden" name="monto_total" id="monto_total" value="0">
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="submit" class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-save mr-1"></i> Registrar Pago
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const matriculaSelect = document.getElementById('id_matricula');
        const cuentasContainer = document.getElementById('cuentas-container');
        const cuentasTbody = document.getElementById('cuentas-tbody');
        const sinCuentas = document.getElementById('sin-cuentas');
        const totalMonto = document.getElementById('total-monto');
        const montoTotalInput = document.getElementById('monto_total');

        function renderCuentas() {
            const selectedOption = matriculaSelect.options[matriculaSelect.selectedIndex];
            const cuentasData = selectedOption.getAttribute('data-cuentas');

            cuentasTbody.innerHTML = '';

            if (!cuentasData) {
                cuentasContainer.classList.add('hidden');
                sinCuentas.classList.remove('hidden');
                actualizarTotal();
                return;
            }

            const cuentas = JSON.parse(cuentasData);

            if (cuentas.length === 0) {
                cuentasContainer.classList.add('hidden');
                sinCuentas.classList.remove('hidden');
                actualizarTotal();
                return;
            }

            cuentasContainer.classList.remove('hidden');
            sinCuentas.classList.add('hidden');

            cuentas.forEach(function(cuenta, index) {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="px-4 py-3 text-sm">${cuenta.concepto}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">${cuenta.referencia}</td>
                    <td class="px-4 py-3 text-sm text-right font-semibold text-red-600">
                        S/. ${parseFloat(cuenta.monto_pendiente).toFixed(2)}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox"
                               class="cuenta-checkbox w-5 h-5 text-green-600 rounded focus:ring-green-500"
                               data-index="${index}"
                               data-monto="${cuenta.monto_pendiente}">
                        <input type="hidden" name="cuentas[${index}][id_cuenta]" value="${cuenta.id_cuenta}" disabled>
                        <input type="hidden" name="cuentas[${index}][monto]"
                               id="monto-cuenta-${index}" value="0" disabled>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <input type="number" step="0.01" min="0" max="${cuenta.monto_pendiente}"
                               class="cuenta-monto w-28 px-2 py-1 border border-gray-300 rounded text-sm text-right"
                               data-index="${index}"
                               value="${parseFloat(cuenta.monto_pendiente).toFixed(2)}"
                               disabled>
                    </td>
                `;
                cuentasTbody.appendChild(tr);
            });

            // Event listeners
            document.querySelectorAll('.cuenta-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const index = this.getAttribute('data-index');
                    const montoInput = document.querySelector(`.cuenta-monto[data-index="${index}"]`);
                    const hiddenCuenta = document.querySelector(`input[name="cuentas[${index}][id_cuenta]"]`);
                    const hiddenMonto = document.getElementById(`monto-cuenta-${index}`);

                    if (this.checked) {
                        montoInput.disabled = false;
                        hiddenCuenta.disabled = false;
                        hiddenMonto.disabled = false;
                        hiddenMonto.value = montoInput.value;
                    } else {
                        montoInput.disabled = true;
                        hiddenCuenta.disabled = true;
                        hiddenMonto.disabled = true;
                        hiddenMonto.value = 0;
                    }
                    actualizarTotal();
                });
            });

            document.querySelectorAll('.cuenta-monto').forEach(function(input) {
                input.addEventListener('input', function() {
                    const index = this.getAttribute('data-index');
                    const hiddenMonto = document.getElementById(`monto-cuenta-${index}`);
                    const max = parseFloat(this.getAttribute('max'));
                    let value = parseFloat(this.value) || 0;

                    if (value > max) {
                        value = max;
                        this.value = max.toFixed(2);
                    }

                    hiddenMonto.value = value;
                    actualizarTotal();
                });
            });
        }

        function actualizarTotal() {
            let total = 0;
            document.querySelectorAll('.cuenta-checkbox:checked').forEach(function(checkbox) {
                const index = checkbox.getAttribute('data-index');
                const montoInput = document.getElementById(`monto-cuenta-${index}`);
                if (montoInput && !montoInput.disabled) {
                    total += parseFloat(montoInput.value) || 0;
                }
            });

            totalMonto.textContent = total.toFixed(2);
            montoTotalInput.value = total.toFixed(2);
        }

        matriculaSelect.addEventListener('change', renderCuentas);

        if (matriculaSelect.value) {
            renderCuentas();
        }
    });
</script>
@endsection
