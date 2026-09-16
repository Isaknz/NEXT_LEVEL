<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Next Level School')</title>
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
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #6d28d9);
            transform: scale(1.02);
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .mobile-menu.open {
            max-height: 700px;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-700 to-purple-700 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo y título -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-school text-blue-700 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Next Level School</h1>
                        <p class="text-xs text-blue-200">Sistema de Gestión Financiera</p>
                    </div>
                </div>

                <!-- Navegación Desktop -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                    <a href="{{ route('alumnos.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-user-graduate mr-1"></i> Alumnos
                    </a>
                    <a href="{{ route('apoderados.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-users mr-1"></i> Apoderados
                    </a>
                    <a href="{{ route('matriculas.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-file-invoice mr-1"></i> Matrículas
                    </a>
                    <a href="{{ route('pagos.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-money-bill mr-1"></i> Pagos
                    </a>
                    <a href="{{ route('gastos.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-receipt mr-1"></i> Gastos
                    </a>
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
                    <a href="{{ route('users.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-user-cog mr-1"></i> Usuarios
                    </a>
                    <a href="{{ route('auditoria.index') }}" class="text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-history mr-1"></i> Auditoría
                    </a>
                    @endif
                </div>

                <!-- Usuario -->
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm font-semibold">{{ auth()->user()->nombre }}</p>
                        <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full capitalize">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm transition" title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden md:inline ml-1">Salir</span>
                        </button>
                    </form>

                    <!-- Botón menú móvil -->
                    <button id="mobile-menu-btn" class="lg:hidden text-white hover:bg-white/10 p-2 rounded-lg transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Menú móvil -->
            <div id="mobile-menu" class="mobile-menu lg:hidden">
                <div class="pt-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('alumnos.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-user-graduate mr-2"></i> Alumnos
                    </a>
                    <a href="{{ route('apoderados.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-users mr-2"></i> Apoderados
                    </a>
                    <a href="{{ route('matriculas.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-file-invoice mr-2"></i> Matrículas
                    </a>
                    <a href="{{ route('pagos.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-money-bill mr-2"></i> Pagos
                    </a>
                    <a href="{{ route('gastos.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-receipt mr-2"></i> Gastos
                    </a>
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'gerente')
                    <a href="{{ route('users.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-user-cog mr-2"></i> Usuarios
                    </a>
                    <a href="{{ route('auditoria.index') }}" class="block text-sm hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-history mr-2"></i> Auditoría
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container mx-auto p-6">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg shadow" id="alerta-success">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="document.getElementById('alerta-success').remove()" class="text-green-500 hover:text-green-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow" id="alerta-error">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button onclick="document.getElementById('alerta-error').remove()" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="container mx-auto px-4 text-center text-sm text-gray-500">
            <p>© {{ date('Y') }} Next Level School - Todos los derechos reservados</p>
        </div>
    </footer>

    <script>
        setTimeout(function() {
            const alertaSuccess = document.getElementById('alerta-success');
            const alertaError = document.getElementById('alerta-error');

            if (alertaSuccess) {
                alertaSuccess.style.transition = 'opacity 0.5s';
                alertaSuccess.style.opacity = '0';
                setTimeout(() => alertaSuccess.remove(), 500);
            }

            if (alertaError) {
                alertaError.style.transition = 'opacity 0.5s';
                alertaError.style.opacity = '0';
                setTimeout(() => alertaError.remove(), 500);
            }
        }, 5000);

        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('open');
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
