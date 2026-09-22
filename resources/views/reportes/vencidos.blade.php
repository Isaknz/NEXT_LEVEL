@extends('layouts.app')
@section('title', 'Pagos Vencidos - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('reportes.index') }}" class="hover:text-blue-700">Reportes</a></li>
    <li class="breadcrumb-separator"><a href="{{ route('reportes.vencidos') }}" class="hover:text-blue-700">Vencidos</a></li>
@endsection
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Pagos Vencidos</h2>
    <p class="text-gray-500 mt-1">Cuentas por cobrar con fecha de vencimiento vencida y estado pendiente.</p>
</div>

<!-- Filter -->
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
            <a href="{{ route('reportes.vencidos') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
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
                    <th class="text-right py-3 px-4 text-gray-700 font-semibold">Monto</th>
                    <th class="text-left py-3 px-4 text-gray-700 font-semibold">Fecha Vencimiento</th>
                    <th class="text-center py-3 px-4 text-gray-700 font-semibold">Días de Mora</th>
                    <th class="text-center py-3 px-4 text-gray-700 font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vencidos as $vencido)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4">
                            {{ $vencido->matricula->alumno->nombres ?? 'N/A' }} {{ $vencido->matricula->alumno->apellidos ?? '' }}
                        </td>
                        <td class="py-3 px-4 text-right">S/. {{ number_format($vencido->monto_pendiente, 2) }}</td>
                        <td class="py-3 px-4">{{ $vencido->fecha_vencimiento ? $vencido->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="font-bold text-red-600">{{ \Carbon\Carbon::parse($vencido->fecha_vencimiento)->diffInDays(now()) }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                {{ $vencido->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">No hay pagos vencidos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200">
        {{ $vencidos->appends(request()->query())->links() }}
    </div>
</div>
@endsection