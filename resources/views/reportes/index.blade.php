@extends('layouts.app')
@section('title', 'Reportes - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('reportes.index') }}" class="hover:text-blue-700">Reportes</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Reportes</h2>
    <p class="text-gray-500 mt-1">Genera y consulta reportes del sistema.</p>
</div>

<!-- Export Buttons -->
<div class="flex flex-wrap gap-4 mb-6">
    <a href="{{ route('reportes.export', ['format' => 'csv']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        <i class="fas fa-file-csv mr-2"></i> Exportar CSV
    </a>
    <a href="{{ route('reportes.export', ['format' => 'excel']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        <i class="fas fa-file-excel mr-2"></i> Exportar Excel
    </a>
    <a href="{{ route('reportes.deudores') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
        <i class="fas fa-file-invoice mr-2"></i> Ver Deudores
    </a>
    <a href="{{ route('reportes.vencidos') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
        <i class="fas fa-clock mr-2"></i> Ver Vencidos
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm text-gray-500 mb-1">Tipo de Reporte</label>
            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos</option>
                <option value="pagos">Pagos</option>
                <option value="gastos">Gastos</option>
                <option value="alumnos">Alumnos</option>
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
            <label class="block text-sm text-gray-500 mb-1">&nbsp;</label>
            <button class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm hover:bg-blue-700 transition">Filtrar</button>
        </div>
    </div>
</div>

<!-- Report Tables -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Reporte de Pagos</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-3 text-gray-600 font-semibold">Alumno</th>
                        <th class="text-left py-2 px-3 text-gray-600 font-semibold">Método</th>
                        <th class="text-right py-2 px-3 text-gray-600 font-semibold">Monto</th>
                        <th class="text-center py-2 px-3 text-gray-600 font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\Pago::with('matricula.alumno')->limit(10)->get() as $pago)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $pago->matricula->alumno->nombre_completo ?? 'N/A' }}</td>
                            <td class="py-2 px-3">{{ $pago->metodo_pago }}</td>
                            <td class="py-2 px-3 text-right">S/. {{ number_format($pago->monto_total, 2) }}</td>
                            <td class="py-2 px-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $pago->estado === 'CONFIRMADO' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $pago->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">No hay pagos registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Reporte de Gastos</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-3 text-gray-600 font-semibold">Concepto</th>
                        <th class="text-left py-2 px-3 text-gray-600 font-semibold">Categoría</th>
                        <th class="text-right py-2 px-3 text-gray-600 font-semibold">Monto</th>
                        <th class="text-center py-2 px-3 text-gray-600 font-semibold">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\Gasto::with('categoria')->limit(10)->get() as $gasto)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $gasto->concepto }}</td>
                            <td class="py-2 px-3">{{ $gasto->categoria->nombre ?? 'N/A' }}</td>
                            <td class="py-2 px-3 text-right">S/. {{ number_format($gasto->monto, 2) }}</td>
                            <td class="py-2 px-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $gasto->estado === 'REGISTRADO' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $gasto->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">No hay gastos registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
