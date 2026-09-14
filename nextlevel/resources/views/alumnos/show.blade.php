<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Alumno - Next Level</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-gradient-to-r from-blue-700 to-purple-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-school text-2xl"></i>
                <h1 class="text-xl font-bold">Next Level School</h1>
            </div>
            <a href="{{ route('alumnos.index') }}" class="text-sm hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </nav>

    <main class="container mx-auto p-6 max-w-4xl">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-user mr-2"></i>Detalle del Alumno
        </h2>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <!-- Encabezado -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
                <h3 class="text-2xl font-bold">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</h3>
                <p class="text-blue-100 mt-1">Código: {{ $alumno->codigo ?? 'N/A' }}</p>
            </div>

            <!-- Datos -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Información Personal</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-gray-500">DNI</label>
                                <p class="font-medium">{{ $alumno->dni ?? 'No registrado' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Fecha de Nacimiento</label>
                                <p class="font-medium">{{ $alumno->fecha_nacimiento ? $alumno->fecha_nacimiento->format('d/m/Y') : 'No registrado' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Sexo</label>
                                <p class="font-medium">{{ $alumno->sexo ?? 'No registrado' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Estado</label>
                                @if($alumno->estado === 'ACTIVO')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">ACTIVO</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">INACTIVO</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Contacto</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-gray-500">Celular</label>
                                <p class="font-medium">{{ $alumno->celular ?? 'No registrado' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Email</label>
                                <p class="font-medium">{{ $alumno->email ?? 'No registrado' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Dirección</label>
                                <p class="font-medium">{{ $alumno->direccion ?? 'No registrado' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Apoderados -->
                <div class="mt-8">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Apoderados</h4>
                    @if($alumno->apoderados->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Parentesco</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Celular</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($alumno->apoderados as $apoderado)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $apoderado->apellidos }}, {{ $apoderado->nombres }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $apoderado->pivot->parentesco }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $apoderado->celular ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No hay apoderados registrados</p>
                    @endif
                </div>
            </div>
        </div>
    </main>
</body>
</html>
