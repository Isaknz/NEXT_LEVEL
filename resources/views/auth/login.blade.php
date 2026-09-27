<x-guest-layout>
    <div class="login-shell">
        <div class="login-layout">
            <section class="login-brand-panel">
                <div>
                    <x-application-logo class="brand-logo" />
                    <p class="mt-8 text-sm font-semibold uppercase tracking-[0.2em] text-blue-100">Next Level</p>
                    <h1 class="mt-3 text-3xl font-bold leading-tight">Gestión que impulsa el futuro.</h1>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-blue-100">Administra matrículas, pagos y gastos desde un solo lugar.</p>
                </div>
                <p class="mt-8 text-xs text-blue-200">Plataforma administrativa institucional</p>
            </section>

            <section class="login-form-panel">
                <div class="mb-8 animate-fade-in">
                    <p class="text-sm font-semibold text-gray-900">Bienvenido de nuevo</p>
                    <h2 class="mt-2 text-2xl font-bold text-gray-900">Inicia sesión</h2>
                    <p class="mt-2 text-sm text-gray-500">Ingresa tus credenciales para continuar.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p class="text-sm">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('status'))
                <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-yellow-700">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span class="text-sm">{{ session('status') }}</span>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}"
                      x-data="{ showPassword: false, loading: false }"
                      x-on:submit="loading = true"
                      class="animate-fade-in">
                    @csrf

                    <div class="mb-5">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Correo electrónico</label>
                        <div class="relative mt-1">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="login-field pl-10"
                                   placeholder="usuario@nextlevel.edu.pe"
                                   required autofocus autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Contraseña</label>
                        <div class="relative mt-1">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input x-bind:type="showPassword ? 'text' : 'password'"
                                   name="password" id="password"
                                   class="login-field pl-10 pr-10"
                                   placeholder="Ingresa tu contraseña"
                                   required autocomplete="current-password">
                            <button type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i x-bind:class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                            Recuérdame
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-700 hover:text-blue-900 font-medium">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit"
                            x-bind:disabled="loading"
                            class="btn-primary w-full rounded-lg px-4 py-3 font-semibold text-white">
                        <template x-if="loading">
                            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!loading">
                            <i class="fas fa-arrow-right">Ingresar al sistema</i>
                        </template>
                    </button>
                </form>

                <div class="mt-8 border-t border-gray-100 pt-5 text-center text-xs text-gray-500">
                    <i class="fas fa-shield-halved mr-1"></i>Acceso seguro para personal autorizado
                </div>
                <p class="mt-2 text-center text-xs text-gray-400">
                    ¿No tienes cuenta? Solicítala al administrador del sistema.
                </p>
            </section>
        </div>
    </div>
</x-guest-layout>
