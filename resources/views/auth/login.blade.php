<x-guest-layout>
    <div class="login-shell">
        <div class="login-layout">

            {{-- Panel izquierdo: identidad institucional --}}
            <section class="login-brand-panel">
                <svg class="brand-arrow" viewBox="0 0 200 200" fill="none" aria-hidden="true" focusable="false">
                    <path d="M20 152 L72 100 L102 130 L172 58" stroke="currentColor" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M132 58 H172 V98" stroke="currentColor" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <div class="brand-lockup">
                    <span class="brand-mark">
                        <x-application-logo class="brand-logo" />
                    </span>
                    <div>
                        <p class="brand-name">Next Level</p>
                        <p class="brand-name">School</p>
                        <p class="brand-motto">Educación en el próximo nivel</p>
                    </div>
                </div>

                <div>
                    <h1 class="brand-headline">
                        Educación que impulsa<br>
                        el <span class="brand-accent">futuro.</span>
                    </h1>
                    <p class="brand-copy">
                        Gestiona matrículas, pagos, estudiantes y procesos académicos desde un solo lugar.
                    </p>

                    <ul class="brand-features">
                        <li class="brand-feature"><i class="fas fa-user-graduate" aria-hidden="true"></i> Matrículas</li>
                        <li class="brand-feature"><i class="fas fa-credit-card" aria-hidden="true"></i> Pagos</li>
                        <li class="brand-feature"><i class="fas fa-chalkboard-user" aria-hidden="true"></i> Gestión académica</li>
                        <li class="brand-feature"><i class="fas fa-chart-simple" aria-hidden="true"></i> Reportes</li>
                    </ul>
                </div>

                <div class="brand-footer">
                    <p>Plataforma administrativa institucional</p>
                    <p class="brand-copyright">
                        &copy; {{ date('Y') }} Next Level School. Todos los derechos reservados.
                    </p>
                </div>
            </section>

            {{-- Panel derecho: formulario de acceso --}}
            <section class="login-form-panel">
                <div class="animate-fade-in">
                    <p class="login-eyebrow">Bienvenido de nuevo</p>
                    <h2 class="login-title">Inicia sesión</h2>
                    <p class="login-subtitle">Ingresa tus credenciales para acceder al sistema.</p>
                </div>

                @if ($errors->any())
                    <div class="login-alert border border-red-200 bg-red-50 text-red-700" role="alert">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-circle mt-0.5" aria-hidden="true"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="login-alert border border-amber-200 bg-amber-50 text-amber-800" role="status">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle mt-0.5" aria-hidden="true"></i>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}"
                      x-data="{ showPassword: false, loading: false }"
                      x-on:submit="loading = true"
                      class="mt-8 animate-fade-in">
                    @csrf

                    <div class="mb-5">
                        <label for="email" class="field-label">Correo electrónico</label>
                        <div class="relative mt-2">
                            <i class="fas fa-envelope field-icon" aria-hidden="true"></i>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="login-field pl-11"
                                   placeholder="usuario@nextlevel.edu.pe"
                                   required autofocus autocomplete="username">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="field-label">Contraseña</label>
                        <div class="relative mt-2">
                            <i class="fas fa-lock field-icon" aria-hidden="true"></i>
                            <input x-bind:type="showPassword ? 'text' : 'password'"
                                   name="password" id="password"
                                   class="login-field pl-11 pr-11"
                                   placeholder="Ingresa tu contraseña"
                                   required autocomplete="current-password">
                            <button type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="field-toggle"
                                    :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <i x-bind:class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <label class="login-remember">
                            <input type="checkbox" name="remember">
                            Recuérdame
                        </label>
                        <a href="{{ route('password.request') }}" class="login-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit" class="btn-login mt-7" x-bind:disabled="loading">
                        <template x-if="loading">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-show="!loading">Ingresar</span>
                        <i x-show="!loading" class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="login-divider">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    <span>Acceso seguro para personal autorizado</span>
                </div>

                <p class="login-legal">
                    ¿No tienes cuenta? Solicítala al administrador del sistema.
                </p>
            </section>

        </div>
    </div>
</x-guest-layout>
