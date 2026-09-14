<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Next Level</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-700 to-purple-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-school text-2xl"></i>
                <div>
                    <h1 class="text-xl font-bold">Next Level School</h1>
                    <p class="text-xs text-blue-200">Sistema de Gestión Financiera</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-semibold">{{ auth()->user()->nombre }}</p>
                    <span class="text-xs bg-white text-blue-700 px-2 py-1 rounded-full capitalize">
                        {{ auth()->user()->role }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm transition duration-300">
                        <i class="fas fa-sign-out-alt mr-1"></i> Salir
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container mx-auto p-6">
        <!-- Bienvenida -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">
                ¡Bienvenido, {{ auth()->user()->nombre }}!
            </h2>
            <p class="text-gray-600 mt-2">Resumen general del sistema</p>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Alumnos -->
            <div class="stat-card bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Alumnos</p>
                        <p class="text-3xl font-bold text-gray-800">{{ \App\Models\Alumno::count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Apoderados -->
            <div class="stat-card bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Apoderados</p>
                        <p class="text-3xl font-bold text-gray-800">{{ \App\Models\Apoderado::count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pagos (Ingresos) -->
            <div class="stat-card bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Ingresos (Pagos)</p>
                        <p class="text-3xl font-bold text-green-600">S/. {{ number_format(\App\Models\Pago::where('estado', 'CONFIRMADO')->sum('monto_total'), 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-down text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Gastos (Egresos) -->
            <div class="stat-card bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Gastos</p>
                        <p class="text-3xl font-bold text-red-600">S/. {{ number_format(\App\Models\Gasto::where('estado', 'REGISTRADO')->sum('monto'), 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Módulos -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-user-cog text-2xl text-purple-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Gestión de Usuarios</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Administrar usuarios del sistema</p>
                <a href="#" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Ver usuarios
                </a>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-user-graduate text-2xl text-blue-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Alumnos</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Gestionar alumnos registrados</p>
                <a href="{{ route('alumnos.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Ver alumnos
                </a>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-file-invoice text-2xl text-indigo-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Matrículas</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Gestionar matrículas</p>
                <a href="#" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Ver matrículas
                </a>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-money-bill text-2xl text-green-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Pagos</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Registrar pagos de alumnos</p>
                <a href="#" class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Gestionar
                </a>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-receipt text-2xl text-red-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Gastos</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Registrar egresos del colegio</p>
                <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Gestionar
                </a>
            </div>

            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Deudores</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Alumnos con pagos pendientes</p>
                <a href="#" class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Ver deudores
                </a>
            </div>

            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <i class="fas fa-history text-2xl text-gray-600 mr-3"></i>
                    <h3 class="text-lg font-semibold">Auditoría</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">Registro de movimientos</p>
                <a href="#" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    Ver historial
                </a>
            </div>
            @endif
        </div>
    </main>
</body>
</html>
