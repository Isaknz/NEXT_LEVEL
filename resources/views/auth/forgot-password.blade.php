<x-guest-layout>
    <div class="login-shell">
        <div class="login-layout is-single">
            <section class="auth-card-body">
                <div class="auth-card-head">
                    <span class="brand-mark">
                        <x-application-logo class="brand-logo" />
                    </span>
                    <div>
                        <p class="auth-brand-name">Next Level School</p>
                        <p class="auth-brand-motto">Educación en el próximo nivel</p>
                    </div>
                </div>

                <div class="animate-fade-in">
                    <p class="login-eyebrow">Recuperar acceso</p>
                    <h1 class="login-title">¿Olvidaste tu contraseña?</h1>
                    <p class="login-subtitle">
                        Indica el correo con el que ingresas y te enviaremos un enlace para que elijas una nueva contraseña.
                    </p>
                </div>

                @if (session('status'))
                    <div class="login-alert border border-blue-200 bg-blue-50 text-blue-800" role="status">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-envelope-circle-check mt-0.5" aria-hidden="true"></i>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-8 animate-fade-in">
                    @csrf

                    <div>
                        <label for="email" class="field-label">Correo electrónico</label>
                        <div class="relative mt-2">
                            <i class="fas fa-envelope field-icon" aria-hidden="true"></i>
                            <input id="email" class="login-field pl-11" type="email" name="email"
                                   value="{{ old('email') }}" placeholder="usuario@nextlevel.edu.pe"
                                   required autofocus autocomplete="username">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="auth-error" />
                    </div>

                    <button type="submit" class="btn-login mt-7">
                        <span>Enviar enlace de recuperación</span>
                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                    </button>
                </form>

                <a href="{{ route('login') }}" class="auth-back">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    Volver al inicio de sesión
                </a>
            </section>
        </div>
    </div>
</x-guest-layout>
