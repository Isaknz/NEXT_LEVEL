@extends('layouts.app')
@section('title', 'Cuentas por Cobrar - Next Level')
@section('breadcrumbs')
    <li class="breadcrumb-separator"><a href="{{ route('cuentas-por-cobrar.index') }}" class="hover:text-blue-700">Cuentas por Cobrar</a></li>
@endsection
@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>Cuentas por Cobrar
        </h2>
        <p class="text-gray-500 text-sm mt-1">Total: {{ $cuentas->total() }} cuenta(s)</p>
    </div>
    <a href="{{ route('cuentas-por-cobrar.create') }}" class="btn-primary text-white px-4 py-2 rounded-lg text-sm shadow">
        <i class="fas fa-plus mr-1"></i> Nueva Cuenta
    </a>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('cuentas-por-cobrar.index', ['estado' => 'PENDIENTE']) }}"
       class="bg-white rounded-xl shadow p-4 hover:shadow-md transition border-l-4 border-blue-500">
        <p class="text-sm text-gray-500">Pendientes</p>
        <p class="text-2xl font-bold text-gray-800">{{ $resumen['PENDIENTE'] }}</p>
    </a>
    <a href="{{ route('cuentas-por-cobrar.index', ['estado' => 'PARCIAL']) }}"
       class="bg-white rounded-xl shadow p-4 hover:shadow-md transition border-l-4 border-yellow-500">
        <p class="text-sm text-gray-500">Parciales</p>
        <p class="text-2xl font-bold text-gray-800">{{ $resumen['PARCIAL'] }}</p>
    </a>
    <a href="{{ route('cuentas-por-cobrar.index', ['estado' => 'PAGADA']) }}"
       class="bg-white rounded-xl shadow p-4 hover:shadow-md transition border-l-4 border-green-500">
        <p class="text-sm text-gray-500">Pagadas</p>
        <p class="text-2xl font-bold text-gray-800">{{ $resumen['PAGADA'] }}</p>
    </a>
    <a href="{{ route('cuentas-por-cobrar.index', ['estado' => 'ANULADA']) }}"
       class="bg-white rounded-xl shadow p-4 hover:shadow-md transition border-l-4 border-gray-400">
        <p class="text-sm text-gray-500">Anuladas</p>
        <p class="text-2xl font-bold text-gray-800">{{ $resumen['ANULADA'] }}</p>
    </a>
</div>

<div class="bg-white rounded-xl shadow-md p-5 mb-6">
    <form method="GET" action="{{ route('cuentas-por-cobrar.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-filter mr-1"></i>Estado
            </label>
            <select name="estado" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Todos</option>
                @foreach(['PENDIENTE', 'PARCIAL', 'PAGADA', 'ANULADA'] as $estado)
                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-file-invoice-dollar mr-1"></i>Concepto
            </label>
            <select name="concepto" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Todos</option>
                @foreach($conceptos as $concepto)
                    <option value="{{ $concepto->id_concepto }}" {{ (string) request('concepto') === (string) $concepto->id_concepto ? 'selected' : '' }}>
                        {{ $concepto->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-600 mb-1">
                <i class="fas fa-search mr-1"></i>Buscar
            </label>
            <input type="text" name="busqueda" value="{{ request('busqueda') }}"
                   placeholder="Referencia, DNI o nombre..."
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('cuentas-por-cobrar.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm shadow transition">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <div class="md:col-span-4">
            <label class="flex items-center text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" name="vencidas" value="1" class="mr-2 rounded border-gray-300"
                       {{ request('vencidas') ? 'checked' : '' }}>
                <i class="fas fa-exclamation-triangle mr-1 text-orange-500"></i>
                Mostrar solo cuentas vencidas (pendientes o parciales con vencimiento anterior a hoy)
            </label>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Referencia</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Alumno</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Concepto</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Emisión / Vence</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Total</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Pendiente</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($cuentas as $cuenta)
                <tr class="hover:bg-blue-50 transition">
                    <td class="px-6 py-4">
                        <p class="text-sm font-mono font-semibold text-gray-700">{{ $cuenta->referencia }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($cuenta->matricula?->alumno)
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $cuenta->matricula->alumno->nombres }} {{ $cuenta->matricula->alumno->apellidos }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $cuenta->matricula->codigo }}</p>
                        @else
                            <span class="text-gray-400 text-sm">Matrícula eliminada</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $cuenta->concepto?->nombre ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        <div>{{ $cuenta->fecha_emision?->format('d/m/Y') }}</div>
                        @if($cuenta->fecha_vencimiento)
                            <div class="{{ $cuenta->estado === 'PAGADA' ? '' : ($cuenta->fecha_vencimiento->isPast() ? 'text-red-600 font-semibold' : 'text-gray-400') }}">
                                v/ {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-700">
                        S/ {{ number_format((float) $cuenta->monto_total, 2) }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-semibold {{ $cuenta->monto_pendiente > 0 && in_array($cuenta->estado, ['PENDIENTE', 'PARCIAL']) ? 'text-red-600' : 'text-gray-400' }}">
                        S/ {{ number_format((float) max(0, $cuenta->monto_pendiente), 2) }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $estilos = [
                                'PENDIENTE' => 'bg-blue-100 text-blue-800',
                                'PARCIAL' => 'bg-yellow-100 text-yellow-800',
                                'PAGADA' => 'bg-green-100 text-green-800',
                                'ANULADA' => 'bg-gray-200 text-gray-700',
                            ];
                        @endphp
                        <span class="{{ $estilos[$cuenta->estado] ?? 'bg-gray-100 text-gray-800' }} px-3 py-1 rounded-full text-xs font-semibold">
                            {{ $cuenta->estado }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            @if($cuenta->estado === 'ANULADA')
                                <span class="text-xs text-gray-500 max-w-[10rem]" title="{{ $cuenta->motivo_anulacion }}">
                                    {{ \Illuminate\Support\Str::limit($cuenta->motivo_anulacion, 20) }}
                                </span>
                            @else
                                <a href="{{ route('cuentas-por-cobrar.edit', $cuenta->id_cuenta) }}"
                                   class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif

                            @if(in_array($cuenta->estado, ['PENDIENTE', 'PARCIAL']))
                                <form action="{{ route('cuentas-por-cobrar.anular', $cuenta->id_cuenta) }}" method="POST"
                                      x-data="{ abierto: false }" class="inline">
                                    @csrf
                                    <button type="button" @click="abierto = true"
                                            class="bg-orange-100 hover:bg-orange-200 text-orange-700 p-2 rounded-lg transition" title="Anular">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <div x-show="abierto" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-cloak>
                                        <div class="bg-white rounded-lg p-6 max-w-md w-full">
                                            <p class="font-semibold mb-1">Anular la cuenta «{{ $cuenta->referencia }}»</p>
                                            <p class="text-sm text-gray-500 mb-3">Indica el motivo de la anulación. Esta acción queda registrada en la auditoría.</p>
                                            <input type="text" name="motivo_anulacion" required maxlength="255" placeholder="Motivo de anulación"
                                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <div class="mt-4 flex gap-2 justify-end">
                                                <button type="button" @click="abierto = false" class="btn-secondary rounded px-3 py-1">Cancelar</button>
                                                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white rounded px-3 py-1">Anular</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endif

                            @if($cuenta->pagoDetalles->isEmpty() && $cuenta->estado !== 'ANULADA')
                                <form action="{{ route('cuentas-por-cobrar.destroy', $cuenta->id_cuenta) }}" method="POST"
                                      x-data="{ confirmDelete: false }" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="confirmDelete = true"
                                            class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div x-show="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
                                        <div class="bg-white rounded-lg p-6 max-w-sm">
                                            <p class="font-semibold">¿Eliminar la cuenta «{{ $cuenta->referencia }}»?</p>
                                            <p class="text-sm text-gray-500 mt-1">Esta acción no se puede deshacer.</p>
                                            <div class="mt-4 flex gap-2 justify-end">
                                                <button type="button" @click="confirmDelete = false" class="btn-secondary rounded px-3 py-1">Cancelar</button>
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-file-invoice text-gray-300 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg font-medium">No hay cuentas por cobrar</p>
                            <p class="text-gray-400 text-sm">Ajusta los filtros o crea una cuenta nueva.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $cuentas->appends(request()->query())->links() }}
</div>
@endsection
