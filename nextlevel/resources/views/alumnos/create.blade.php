<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Alumno - Next Level</title>
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
            <i class="fas fa-user-plus mr-2 text-blue-600"></i>Registrar Nuevo Alumno
        </h2>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
                    <span class="font-bold">Por favor corrige los siguientes errores:</span>
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('alumnos.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-barcode mr-1"></i>Código
                    </label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}"
                           placeholder="Ej: A001"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-1"></i>DNI
                    </label>
                    <input type="text" name="dni" value="{{ old('dni') }}" maxlength="8"
                           placeholder="8 dígitos"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>Nombres *
                    </label>
                    <input type="text" name="nombres" value="{{ old('nombres') }}" required
                           placeholder="Nombres del alumno"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-users mr-1"></i>Apellidos *
                    </label>
                    <input type="text" name="apellidos" value="{{ old('apellidos') }}" required
                           placeholder="Apellidos del alumno"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-1"></i>Fecha de Nacimiento
                    </label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-venus-mars mr-1"></i>Sexo
                    </label>
                    <select name="sexo" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Seleccionar</option>
                        <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="OTRO" {{ old('sexo') === 'OTRO' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap mr-1"></i>Grado
                    </label>
                    <select name="id_grado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Seleccionar grado</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id_grado }}" {{ old('id_grado') == $grado->id_grado ? 'selected' : '' }}>
                                {{ $grado->nombre }} - {{ $grado->nivel->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone mr-1"></i>Celular
                    </label>
                    <input type="text" name="celular" value="{{ old('celular') }}"
                           placeholder="999888777"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1"></i>Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="correo@ejemplo.com"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-1"></i>Dirección
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                           placeholder="Dirección del alumno"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-1"></i>Estado
                    </label>
                    <select name="estado" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="ACTIVO" {{ old('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg text-sm shadow transition">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg text-sm font-semibold shadow transition transform hover:scale-105">
                    <i class="fas fa-save mr-1"></i> Guardar Alumno
                </button>
            </div>
        </form>
    </main>
</body>
</html>
