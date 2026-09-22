@extends('layouts.app')

@section('title', 'Registrar Pago - Next Level')

@section('content')
<div class="max-w-5xl mx-auto" x-data="{ showSummary: false, selectedConcepts: [], metodoPagoSeleccionado: '' }">
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
                <select name="metodo_pago" id="metodo_pago" required
                        x-model="metodoPagoSeleccionado"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
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

        <!-- Botones de acción -->
        <div class="mt-8 flex justify-end gap-3 border-t pt-6">
            <a href="{{ route('pagos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                Cancelar
            </a>
            <button type="button" id="btn-ver-resumen"
                    @click="showSummary = true"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow">
                <i class="fas fa-eye mr-1"></i> Ver Resumen
            </button>
            <button type="submit" id="btn-registrar"
                    class="btn-primary text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow opacity-50 cursor-not-allowed"
                    disabled>
                <i class="fas fa-save mr-1"></i> Registrar Pago
            </button>
        </div>
    </form>

    <!-- Modal de Resumen -->
    <div x-show="showSummary"
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6" @click.away="showSummary = false">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-file-invoice mr-2 text-green-600"></i>Resumen del Pago
            </h3>

            <!-- Conceptos seleccionados -->
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Conceptos Seleccionados</h4>
                <div id="resumen-conceptos" x-text="selectedConcepts.length === 0 ? 'No hay conceptos seleccionados' : ''" class="text-sm text-gray-500 mb-2"></div>
                <ul class="space-y-2 max-h-48 overflow-y-auto" id="resumen-lista">
                </ul>
            </div>

            <!-- Resumen por concepto -->
            <div class="mb-4 p-4 bg-gray-50 rounded-lg" id="resumen-subtotales">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Desglose por Concepto</h4>
                <div id="subtotales-concepto" class="text-sm text-gray-600"></div>
            </div>

            <!-- Total Conceptos -->
            <div class="mb-4">
                <span class="text-sm font-semibold text-gray-700">Total de Conceptos:</span>
                <span class="ml-2 text-lg font-bold text-gray-800" id="resumen-total-conceptos">0</span>
            </div>

            <!-- Gran Total -->
            <div class="mb-6 p-4 bg-green-100 rounded-lg border border-green-300">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-800">Gran Total:</span>
                    <span class="text-2xl font-bold text-green-600">
                        S/. <span id="resumen-grand-total">0.00</span>
                    </span>
                </div>
            </div>

            <!-- Método de Pago -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Método de Pago (Confirmado)</label>
                <select name="metodo_pago_confirmado" id="metodo_pago_confirmado"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="YAPE">Yape</option>
                    <option value="PLIN">Plin</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="TARJETA">Tarjeta</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>

            <!-- Botones del modal -->
            <div class="flex justify-end gap-3">
                <button type="button"
                        @click="showSummary = false"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow">
                    Cerrar
                </button>
                <button type="button"
                        @click="confirmarResumen()"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow">
                    <i class="fas fa-check mr-1"></i> Confirmar y Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const matriculaSelect = document.getElementById('id_matricula');
        const cuentasContainer = document.getElementById('cuentas-container');
        const cuentasTbody = document.getElementById('cuentas-tbody');
        const sinCuentas = document.getElementById('sin-cuentas');
        const totalMonto = document.getElementById('total-monto');
        const montoTotalInput = document.getElementById('monto_total');
        const btnVerResumen = document.getElementById('btn-ver-resumen');
        const btnRegistrar = document.getElementById('btn-registrar');
        const metodoPagoSelect = document.getElementById('metodo_pago');
        const metodoPagoConfirmado = document.getElementById('metodo_pago_confirmado');
        const resumenLista = document.getElementById('resumen-lista');
        const resumenTotalConceptos = document.getElementById('resumen-total-conceptos');
        const resumenGrandTotal = document.getElementById('resumen-grand-total');
        const subtotalesConcepto = document.getElementById('subtotales-concepto');

        let selectedConcepts = [];
        let showSummary = false;

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
                               data-monto="${cuenta.monto_pendiente}"
                               data-concepto="${cuenta.concepto}"
                               data-referencia="${cuenta.referencia}"
                               data-id-cuenta="${cuenta.id_cuenta}">
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

            document.querySelectorAll('.cuenta-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const index = this.getAttribute('data-index');
                    const concepto = this.getAttribute('data-concepto');
                    const referencia = this.getAttribute('data-referencia');
                    const idCuenta = this.getAttribute('data-id-cuenta');
                    const monto = parseFloat(this.getAttribute('data-monto'));
                    const montoInput = document.querySelector(`.cuenta-monto[data-index="${index}"]`);
                    const hiddenCuenta = document.querySelector(`input[name="cuentas[${index}][id_cuenta]"]`);
                    const hiddenMonto = document.getElementById(`monto-cuenta-${index}`);

                    if (this.checked) {
                        montoInput.disabled = false;
                        hiddenCuenta.disabled = false;
                        hiddenMonto.disabled = false;
                        hiddenMonto.value = montoInput.value;
                        seleccionarConcepto(index, concepto, referencia, idCuenta, montoInput.value);
                    } else {
                        montoInput.disabled = true;
                        hiddenCuenta.disabled = true;
                        hiddenMonto.disabled = true;
                        hiddenMonto.value = 0;
                        deseleccionarConcepto(index);
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
                    actualizarConceptoMonto(index, value);
                    actualizarTotal();
                });
            });
        }

        function seleccionarConcepto(index, concepto, referencia, idCuenta, monto) {
            const existe = selectedConcepts.find(c => c.index === index);
            if (!existe) {
                selectedConcepts.push({
                    index: index,
                    concepto: concepto,
                    referencia: referencia,
                    id_cuenta: idCuenta,
                    monto: parseFloat(monto)
                });
            }
        }

        function deseleccionarConcepto(index) {
            selectedConcepts = selectedConcepts.filter(c => c.index !== index);
        }

        function actualizarConceptoMonto(index, monto) {
            const concepto = selectedConcepts.find(c => c.index === index);
            if (concepto) {
                concepto.monto = parseFloat(monto);
            }
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

        function actualizarResumen() {
            resumenLista.innerHTML = '';
            let grandTotal = 0;

            const subtotales = {};
            selectedConcepts.forEach(function(c) {
                grandTotal += c.monto;
                if (!subtotales[c.concepto]) {
                    subtotales[c.concepto] = 0;
                }
                subtotales[c.concepto] += c.monto;
            });

            if (selectedConcepts.length === 0) {
                resumenLista.innerHTML = '<li class="text-sm text-gray-500">No hay conceptos seleccionados</li>';
            } else {
                selectedConcepts.forEach(function(c) {
                    const li = document.createElement('li');
                    li.className = 'flex justify-between text-sm p-2 bg-gray-50 rounded';
                    li.innerHTML = `
                        <span>${c.concepto} (${c.referencia})</span>
                        <span class="font-semibold">S/. ${c.monto.toFixed(2)}</span>
                    `;
                    resumenLista.appendChild(li);
                });
            }

            resumenTotalConceptos.textContent = selectedConcepts.length;
            resumenGrandTotal.textContent = grandTotal.toFixed(2);

            subtotalesConcepto.innerHTML = '';
            Object.entries(subtotales).forEach(function(entry) {
                    const concepto = entry[0];
                    const total = entry[1];
                    const div = document.createElement('div');
                    div.className = 'flex justify-between text-sm mb-1';
                    div.innerHTML = `
                        <span>${concepto}</span>
                        <span>S/. ${total.toFixed(2)}</span>
                    `;
                    subtotalesConcepto.appendChild(div);
                });

            if (Object.keys(subtotales).length === 0) {
                subtotalesConcepto.innerHTML = '<div class="text-sm text-gray-500">Sin conceptos</div>';
            }
        }

        function confirmarResumen() {
            if (selectedConcepts.length === 0) {
                alert('Por favor, seleccione al menos un concepto.');
                return;
            }
            if (!metodoPagoConfirmado.value) {
                alert('Por favor, seleccione un método de pago.');
                return;
            }
            // Actualizar el método de pago oculto con el confirmado
            metodoPagoSelect.value = metodoPagoConfirmado.value;
            showSummary = false;
            // Habilitar el botón de registrar
            btnRegistrar.disabled = false;
            btnRegistrar.classList.remove('opacity-50', 'cursor-not-allowed');
            btnRegistrar.classList.add('opacity-100', 'cursor-pointer');
        }

        // Exponer funciones a Alpine
        window.selectedConcepts = selectedConcepts;
        window.showSummary = showSummary;
        window.confirmarResumen = confirmarResumen;

        // Observar cambios en selectedConcepts para actualizar el resumen
        const observer = new MutationObserver(function() {
            actualizarResumen();
        });

        matriculaSelect.addEventListener('change', renderCuentas);

        if (matriculaSelect.value) {
            renderCuentas();
        }

        // Actualizar resumen cuando cambien las cantidades
        setInterval(function() {
            actualizarResumen();
        }, 500);
    });
</script>
@endsection