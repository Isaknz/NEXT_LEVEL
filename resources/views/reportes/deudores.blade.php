@extends('layouts.app')
@section('title', 'Deudores - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('reportes.index') }}" class="hover:text-blue-700">Reportes</a></li>
    <li class="breadcrumb-separator"><a href="{{ route('reportes.deudores') }}" class="hover:text-blue-700">Deudores</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Deudores</h2>
    <p class="text-gray-500 mt-1">Alumnos con pagos pendientes o parciales.</p>
</div>

<!-- Filtro -->
<div class="bg-white rounded-xl shadow p-6 mb-6">
    <form method="GET" class="flex items-end gap-4">
        <div>
            <label class="block text-sm text-gray-500 mb-1">Periodo Académico</label>
            <select name="periodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los periodos</option>
                @foreach($periodos as $periodo)
                    <option value="{{ $periodo->id_periodo }}" {{ $periodoId == $periodo->id_periodo ? 'selected' : '' }}>{{ $periodo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                <i class="fas fa-filter mr-2"></i> Filtrar
            </button>
            <a href="{{ route('reportes.deudores') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                Limpiar
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-200">
                    <th class="text-left py-3 px-4 text-gray-700 font-semibold">Alumno</th>
                    <th class="text-left py-3 px-4 text-gray-700 font-semibold">Apoderado</th>
                    <th class="text-left py-3 px-4 text-gray-700 font-semibold">Matrícula</th>
                    <th class="text-right py-3 px-4 text-gray-700 font-semibold">Monto Total</th>
                    <th class="text-right py-3 px-4 text-gray-700 font-semibold">Monto Pagado</th>
                    <th class="text-right py-3 px-4 text-gray-700 font-semibold">Saldo Pendiente</th>
                    <th class="text-left py-3 px-4 text-gray-700 font-semibold">Fecha Vencimiento</th>
                    <th class="text-center py-3 px-4 text-gray-700 font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deudores as $deuda)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $deuda->matricula->alumno->nombres ?? 'N/A' }} {{ $deuda->matricula->alumno->apellidos ?? '' }}</td>
                        <td class="py-3 px-4">
                            {{ $deuda->matricula->alumno->apoderado ? $deuda->matricula->alumno->apoderado->apellidos . ', ' . $deuda->matricula->alumno->apoderado->nombres : 'N/A' }}
                        </td>
                        <td class="py-3 px-4">{{ $deuda->matricula->codigo ?? 'N/A' }}</td>
                        <td class="py-3 px-4 text-right">S/. {{ number_format($deuda->monto_total ?? $deuda->monto_original, 2) }}</td>
                        <td class="py-3 px-4 text-right">S/. {{ number_format($deuda->monto_total - $deuda->monto_pendiente, 2) }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-red-600">S/. {{ number_format($deuda->monto_pendiente, 2) }}</td>
                        <td class="py-3 px-4">{{ $deuda->fecha_vencimiento ? $deuda->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $deuda->estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $deuda->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">No hay deudores registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">
        {{ $deudores->appends(request()->query())->links() }}
    </div>
</div>
@endsection