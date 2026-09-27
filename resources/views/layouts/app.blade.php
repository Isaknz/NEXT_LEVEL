<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Next Level School')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100">
    <!-- SIDEBAR -->
    <aside x-data="{ open: true }"
           class="fixed left-0 top-0 h-full w-64 bg-blue-950 text-white z-40 transform -translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full'"
           id="sidebar">
        <!-- Logo -->
        <div class="flex items-center justify-between px-4 py-4 border-b border-blue-800">
            <div class="flex items-center space-x-2">
                <x-application-logo class="h-9 w-9 text-white" />
                <div>
                    <h1 class="text-sm font-bold leading-tight">Next Level</h1>
                    <p class="text-[10px] text-blue-300">School</p>
                </div>
            </div>
            <button @click="open = !open" class="text-blue-300 hover:text-white lg:hidden">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
        </div>

        <!-- NavegaciÃ³n -->
        <nav class="px-2 py-3 space-y-1" class="space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('dashboard') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-home w-5"></i>
                <span class="ms-3">Dashboard</span>
            </a>

            <!-- Alumnos -->
            @if(Route::has('alumnos.index') && auth()->user()->puedeVerModulo('alumnos'))
            <a href="{{ route('alumnos.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('alumnos.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-user-graduate w-5"></i>
                <span class="ms-3">Alumnos</span>
            </a>
            @endif

            <!-- Apoderados -->
            @if(Route::has('apoderados.index') && auth()->user()->puedeVerModulo('apoderados'))
            <a href="{{ route('apoderados.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('apoderados.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-users w-5"></i>
                <span class="ms-3">Apoderados</span>
            </a>
            @endif

            <!-- MatrÃ­culas -->
            @if(Route::has('matriculas.index') && auth()->user()->puedeVerModulo('matriculas'))
            <a href="{{ route('matriculas.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('matriculas.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-file-invoice w-5"></i>
                <span class="ms-3">MatrÃ­culas</span>
            </a>
            @endif

            <!-- Cuentas por Cobrar -->
            @if(Route::has('cuentas-por-cobrar.index') && auth()->user()->puedeVerModulo('cuentas-por-cobrar'))
            <a href="{{ route('cuentas-por-cobrar.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('cuentas-por-cobrar.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-file-invoice-dollar w-5"></i>
                <span class="ms-3">Cuentas por Cobrar</span>
            </a>
            @endif

            <!-- Pagos -->
            @if(Route::has('pagos.index') && auth()->user()->puedeVerModulo('pagos'))
            <a href="{{ route('pagos.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('pagos.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-money-bill-wave w-5"></i>
                <span class="ms-3">Pagos</span>
            </a>
            @endif

            <!-- Cajas -->
            @if(Route::has('cajas.index') && auth()->user()->puedeVerModulo('cajas'))
            <a href="{{ route('cajas.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('cajas.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-landmark w-5"></i>
                <span class="ms-3">Cajas</span>
            </a>
            @endif

            <!-- Gastos -->
            @if(Route::has('gastos.index') && auth()->user()->puedeVerModulo('gastos'))
            <a href="{{ route('gastos.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('gastos.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-receipt w-5"></i>
                <span class="ms-3">Gastos</span>
            </a>
            @endif

            <!-- Divisor -->
            <hr class="border-blue-800 my-3">

            <!-- Reportes -->
            @if(Route::has('reportes.index') && auth()->user()->puedeVerModulo('reportes'))
            <a href="{{ route('reportes.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('reportes.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-chart-bar w-5"></i>
                <span class="ms-3">Reportes</span>
            </a>
            @endif

            <!-- ConfiguraciÃ³n (colapsable) -->
            @if((Route::has('periodos.index') && auth()->user()->puedeVerModulo('periodos')) || (Route::has('niveles.index') && auth()->user()->puedeVerModulo('niveles')))
            <div x-data="{ configOpen: false }" class="space-y-1">
                <button @click="configOpen = !configOpen" class="w-full flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 text-blue-200">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ms-3 flex-1 text-left">ConfiguraciÃ³n</span>
                    <i x-show="!configOpen" class="fas fa-chevron-down text-xs"></i>
                    <i x-show="configOpen" class="fas fa-chevron-up text-xs"></i>
                </button>
                <div x-show="configOpen" class="space-y-1 pl-4 mt-1">
                    @if(Route::has('periodos.index') && auth()->user()->puedeVerModulo('periodos'))
                    <a href="{{ route('periodos.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('periodos*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-calendar w-4"></i><span class="ms-2">Periodos</span>
                    </a>
                    @endif
                    @if(Route::has('niveles.index') && auth()->user()->puedeVerModulo('niveles'))
                    <a href="{{ route('niveles.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('niveles*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-layer-group w-4"></i><span class="ms-2">Niveles</span>
                    </a>
                    @endif
                    @if(Route::has('grados.index') && auth()->user()->puedeVerModulo('grados'))
                    <a href="{{ route('grados.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('grados*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-list-ol w-4"></i><span class="ms-2">Grados</span>
                    </a>
                    @endif
                    @if(Route::has('facultades.index') && auth()->user()->puedeVerModulo('facultades'))
                    <a href="{{ route('facultades.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('facultades*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-university w-4"></i><span class="ms-2">Facultades</span>
                    </a>
                    @endif
                    @if(Route::has('ciclos.index') && auth()->user()->puedeVerModulo('ciclos'))
                    <a href="{{ route('ciclos.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('ciclos*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-sync w-4"></i><span class="ms-2">Ciclos</span>
                    </a>
                    @endif
                    @if(Route::has('conceptos.index') && auth()->user()->puedeVerModulo('conceptos'))
                    <a href="{{ route('conceptos.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('conceptos*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-tags w-4"></i><span class="ms-2">Conceptos</span>
                    </a>
                    @endif
                    @if(Route::has('categorias.index') && auth()->user()->puedeVerModulo('categorias'))
                    <a href="{{ route('categorias.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('categorias*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-folder w-4"></i><span class="ms-2">CategorÃ­as</span>
                    </a>
                    @endif
                    @if(Route::has('cajas.index') && auth()->user()->puedeVerModulo('cajas'))
                    <a href="{{ route('cajas.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('cajas*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-coins w-4"></i><span class="ms-2">Cajas</span>
                    </a>
                    @endif
                    @if(Route::has('users.index') && auth()->user()->puedeVerModulo('users'))
                    <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-blue-900 text-blue-300 {{ request()->is('users*') ? 'bg-blue-900 text-white' : '' }}">
                        <i class="fas fa-user-cog w-4"></i><span class="ms-2">Usuarios</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- AuditorÃ­a -->
            @if((auth()->user()->role === 'admin' || auth()->user()->role === 'gerente') && Route::has('auditoria.index'))
            <a href="{{ route('auditoria.index') }}" class="flex items-center px-3 py-2.5 text-sm rounded-lg hover:bg-blue-900 {{ request()->routeIs('auditoria.*') ? 'bg-blue-900 text-white' : 'text-blue-200' }}">
                <i class="fas fa-history w-5"></i>
                <span class="ms-3">AuditorÃ­a</span>
            </a>
            @endif
        </nav>

        <!-- Usuario -->
        <div class="absolute bottom-0 left-0 right-0 border-t border-blue-800 p-3">
            <div class="flex items-center px-2">
                <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center text-xs font-bold">
                    {{ substr(auth()->user()->nombre, 0, 1) }}
                </div>
                <div class="ms-3 flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate">{{ auth()->user()->nombre }}</p>
                    <span class="text-[10px] bg-blue-700 px-1.5 py-0.5 rounded-full capitalize">
                        {{ auth()->user()->role }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-1">
                    @csrf
                    <button type="submit" class="text-blue-300 hover:text-red-400 text-xs" title="Cerrar sesiÃ³n">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- OVERLAY MÃ“VIL -->
    <div x-show="open"
         x-transition.opacity
         @click="open = false"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"
         id="sidebar-overlay"></div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="lg:ml-64" id="main-content">
        <!-- HEADER -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-3">
                    <!-- BotÃ³n toggle sidebar -->
                    <button @click="open = !open" class="text-gray-600 hover:text-blue-700 p-1">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <!-- Breadcrumbs -->
                    <nav aria-label="Breadcrumb" class="text-sm text-gray-500 hidden md:block">
                        <ol class="flex items-center space-x-1">
                            <li><a href="{{ route('dashboard') }}" class="hover:text-blue-700">Inicio</a></li>
                            @yield('breadcrumbs')
                        </ol>
                    </nav>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Periodo Activo -->
                    <div class="hidden md:flex items-center bg-blue-50 border border-blue-200 rounded-lg px-3 py-1.5">
                        <i class="fas fa-calendar-alt text-blue-700 text-xs mr-2"></i>
                        <span class="text-xs font-semibold text-blue-900">
                            @php
                                $periodo = \App\Models\PeriodoAcademico::where('estado', 'ABIERTO')->first();
                                echo $periodo ? $periodo->nombre . ' (' . $periodo->anio . ')' : 'Sin periodo';
                            @endphp
                        </span>
                    </div>

                    <!-- Alertas -->
                    <button class="relative text-gray-600 hover:text-blue-700" title="Notificaciones">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center">3</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- CONTENIDO -->
        <main class="p-4 md:p-6">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-r-lg shadow flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.closest('.bg-green-50').remove()" class="text-green-600 hover:text-green-800"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg shadow flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.closest('.bg-red-50').remove()" class="text-red-600 hover:text-red-800"><i class="fas fa-times"></i></button>
                </div>
            @endif

            @if(session('status'))
                <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 rounded-r-lg shadow">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-3"></i>
                        <span class="font-medium">{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 mt-6">
            <div class="container mx-auto px-4 text-center text-sm text-gray-500">
                <p>Â© {{ date('Y') }} Next Level School â€” Sistema de GestiÃ³n Educativa. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>

    <script>
        // Toggle sidebar en mÃ³vil
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar && overlay) {
                window.__toggleSidebar = function() {
                    sidebar.classList.toggle('translate-x-0');
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('hidden');
                };
            }
        });

        setTimeout(function() {
            document.querySelectorAll('.bg-green-50, .bg-red-50, .bg-blue-50').forEach(function(el) {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>
