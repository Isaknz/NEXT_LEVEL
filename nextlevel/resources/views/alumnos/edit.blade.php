<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Alumno - Next Level</title>
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
            <i class="fas fa-user-edit mr-2"></i>Editar Alumno
        </h2>

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('alumnos.update', $alumno->id_alumno) }}" method="POST" class="bg-white rounded-xl shadow p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Código</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $alumno->codigo) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">DNI</label>
                    <input type="text" name="dni" value="{{ old('dni', $alumno->dni) }}" maxlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombres *</label>
                    <input type="text" name="nombres" value="{{ old('nombres', $alumno->nombres) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Apellidos *</label>
                    <input type="text" name="apellidos" value="{{ old('apellidos', $alumno->apellidos) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ? $alumno->fecha_nacimiento->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sexo</label>
                    <select name="sexo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar</option>
                        <option value="F" {{ old('sexo', $alumno->sexo) === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="M" {{ old('sexo', $alumno->sexo) === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="OTRO" {{ old('sexo', $alumno->sexo) === 'OTRO' ? 'selected' : '' }}>Otro</option>
                        <option value="NO_DECLARA" {{ old('sexo', $alumno->sexo) === 'NO_DECLARA' ? 'selected' : '' }}>No declara</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Celular</label>
                    <input type="text" name="celular" value="{{ old('celular', $alumno->celular) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $alumno->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $alumno->direccion) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="ACTIVO" {{ old('estado', $alumno->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVO" {{ old('estado', $alumno->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm">
                    <i class="fas fa-save mr-1"></i> Actualizar
                </button>
            </div>
        </form>
    </main>
</body>
</html>
