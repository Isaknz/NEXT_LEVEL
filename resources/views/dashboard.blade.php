@extends('layouts.app')

@section('title', 'Dashboard - Next Level')

@section('content')
<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">
        ¡Bienvenido, {{ auth()->user()->nombre }}!
    </h2>
    <p class="text-gray-600 mt-2">Resumen general del sistema</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Alumnos</p>
                <p class="text-3xl font-bold text-gray-800">{{ $total_alumnos ?? \App\Models\Alumno::count() }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Alumnos Activos</p>
                <p class="text-3xl font-bold text-green-600">{{ $alumnos_activos ?? \App\Models\Alumno::where('estado', 'ACTIVO')->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Apoderados</p>
                <p class="text-3xl font-bold text-gray-800">{{ $total_apoderados ?? \App\Models\Apoderado::count() }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Ingresos</p>
                <p class="text-3xl font-bold text-green-600">S/. {{ number_format($total_ingresos ?? \App\Models\Pago::where('estado', 'CONFIRMADO')->sum('monto_total'), 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                <i class="fas fa-arrow-down text-emerald-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Egresos</p>
                <p class="text-3xl font-bold text-red-600">S/. {{ number_format($total_egresos ?? \App\Models\Gasto::where('estado', 'REGISTRADO')->sum('monto'), 2) }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-arrow-up text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Deudores</p>
                <p class="text-3xl font-bold text-orange-600">{{ $total_deudores ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pagos Vencidos</p>
                <p class="text-3xl font-bold text-red-600">{{ $total_vencidos ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="stat-card bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Matrículas Activas</p>
                <p class="text-3xl font-bold text-blue-600">{{ $matriculas_activas ?? \App\Models\Matricula::where('estado', 'ACTIVA')->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Cajas Saldo -->
@if(isset($saldos_cajas) && $saldos_cajas->count())
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Saldos por Caja</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 px-3 text-gray-600 font-semibold">Caja</th>
                    <th class="text-right py-2 px-3 text-gray-600 font-semibold">Saldo Inicial</th>
                    <th class="text-right py-2 px-3 text-gray-600 font-semibold">Saldo Actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saldos_cajas as $caja)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-2 px-3">{{ $caja->nombre }}</td>
                    <td class="py-2 px-3 text-right">S/. {{ number_format($caja->saldo_inicial, 2) }}</td>
                    <td class="py-2 px-3 text-right font-semibold">S/. {{ number_format($caja->saldo_actual, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Filters Section -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtros de Reportes</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm text-gray-500 mb-1">Periodo Académico</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los periodos</option>
                @foreach(\App\Models\PeriodoAcademico::all() as $periodo)
                    <option value="{{ $periodo->id_periodo }}">{{ $periodo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-500 mb-1">Fecha Desde</label>
            <input type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm text-gray-500 mb-1">Fecha Hasta</label>
            <input type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm text-gray-500 mb-1">Caja</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas las cajas</option>
                @foreach(\App\Models\Caja::all() as $caja)
                    <option value="{{ $caja->id_caja }}">{{ $caja->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ingresos vs Egresos por Mes</h3>
        <canvas id="ingresosEgresosChart"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pagos por Método</h3>
        <canvas id="pagosMetodoChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ingresosMensuales = {{ json_encode($ingresos_mensuales ?? []) }};
    var egresosMensuales = {{ json_encode($egresos_mensuales ?? []) }};
    var pagosPorMetodo = {{ json_encode($pagos_por_metodo ?? []) }};

    var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    var ingresosData = meses.map(function(m, i) { return ingresosMensuales[i + 1] ? ingresosMensuales[i + 1].total : 0; });
    var egresosData = meses.map(function(m, i) { return egresosMensuales[i + 1] ? egresosMensuales[i + 1].total : 0; });

    new Chart(document.getElementById('ingresosEgresosChart'), {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ingresos',
                data: ingresosData,
                backgroundColor: '#2474bd'
            }, {
                label: 'Egresos',
                data: egresosData,
                backgroundColor: '#c83c4a'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: { font: { size: 12 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return 'S/. ' + value; }
                    }
                }
            }
        }
    });

    var metodoLabels = pagosPorMetodo.map(function(m) { return m.metodo_pago; });
    var metodoData = pagosPorMetodo.map(function(m) { return m.total; });

    new Chart(document.getElementById('pagosMetodoChart'), {
        type: 'doughnut',
        data: {
            labels: metodoLabels,
            datasets: [{
                data: metodoData,
                backgroundColor: ['#2474bd', '#1d5d9b', '#c83c4a', '#102a43', '#163d67', '#e7f1fb']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 12 } }
                }
            }
        }
    });
</script>

<!-- Last Movements -->
@if(isset($ultimos_movimientos) && $ultimos_movimientos->count())
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Últimos Movimientos</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-2 px-3 text-gray-600 font-semibold">Usuario</th>
                    <th class="text-left py-2 px-3 text-gray-600 font-semibold">Acción</th>
                    <th class="text-left py-2 px-3 text-gray-600 font-semibold">Módulo</th>
                    <th class="text-left py-2 px-3 text-gray-600 font-semibold">Descripción</th>
                    <th class="text-left py-2 px-3 text-gray-600 font-semibold">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ultimos_movimientos as $mov)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-2 px-3">{{ $mov->usuario_nombre ?? 'Sistema' }}</td>
                    <td class="py-2 px-3"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ $mov->accion }}</span></td>
                    <td class="py-2 px-3">{{ $mov->modulo }}</td>
                    <td class="py-2 px-3">{{ \Str::limit($mov->descripcion, 50) }}</td>
                    <td class="py-2 px-3">{{ $mov->created_at ? $mov->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection