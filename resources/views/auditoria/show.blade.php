@extends('layouts.app')

@section('title', 'Detalle Movimiento - Next Level')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-search mr-2 text-gray-600"></i>Detalle del Movimiento
        </h2>
        <a href="{{ route('auditoria.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm shadow">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Encabezado -->
        <div class="bg-gradient-to-r from-gray-700 to-gray-900 text-white p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-{{ $movimiento->accion_color }}-500 rounded-full flex items-center justify-center mr-4">
                        <i class="fas {{ $movimiento->accion_icono }} text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold">{{ $movimiento->accion }}</h3>
                        <p class="text-gray-300 mt-1">{{ $movimiento->modulo }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-300">{{ $movimiento->created_at->format('d/m/Y') }}</p>
                    <p class="text-2xl font-bold">{{ $movimiento->created_at->format('H:i:s') }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Info general -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Usuario</h4>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $movimiento->usuario_nombre }}</p>
                            <p class="text-sm text-gray-500">{{ $movimiento->user->email ?? 'N/A' }}</p>
                            <span class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full capitalize">
                                {{ $movimiento->user->role ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detalles técnicos</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">IP:</span>
                            <span class="text-sm font-mono font-medium">{{ $movimiento->ip_address ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Entidad:</span>
                            <span class="text-sm font-medium">{{ $movimiento->entidad ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">ID Entidad:</span>
                            <span class="text-sm font-medium">{{ $movimiento->entidad_id ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            @if($movimiento->descripcion)
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Descripción</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">{{ $movimiento->descripcion }}</p>
                </div>
            </div>
            @endif

            <!-- Valores anteriores y nuevos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($movimiento->valores_anteriores)
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">
                        <i class="fas fa-history text-red-500 mr-1"></i>Valores anteriores
                    </h4>
                    <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                        <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($movimiento->valores_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif

                @if($movimiento->valores_nuevos)
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">
                        <i class="fas fa-arrow-right text-green-500 mr-1"></i>Valores nuevos
                    </h4>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                        <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($movimiento->valores_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
