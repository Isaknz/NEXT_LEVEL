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
                    <p class="login-eyebrow">Seguridad de la cuenta</p>
                    <h1 class="login-title">Elige una nueva contraseña</h1>
                    <p class="login-subtitle">
                        Confirma tu correo y define una contraseña nueva para recuperar el acceso.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.reset.update') }}"
                      x-data="{ show: false, showConfirm: false }"
                      class="mt-8 animate-fade-in">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <label for="email" class="field-label">Correo electrónico</label>
                        <div class="relative mt-2">
                            <i class="fas fa-envelope field-icon" aria-hidden="true"></i>
                            <input id="email" class="login-field pl-11" type="email" name="email"
                                   value="{{ old('email', $request->email) }}"
                                   placeholder="usuario@nextlevel.edu.pe"
                                   required autofocus autocomplete="username">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="auth-error" />
                    </div>

                    <div class="mt-5">
                        <label for="password" class="field-label">Nueva contraseña</label>
                        <div class="relative mt-2">
                            <i class="fas fa-lock field-icon" aria-hidden="true"></i>
                            <input x-bind:type="show ? 'text' : 'password'"
                                   id="password" class="login-field pl-11 pr-11" type="password"
                                   name="password" placeholder="Ingresa tu contraseña"
                                   required autocomplete="new-password">
                            <button type="button" x-on:click="show = !show" class="field-toggle"
                                    :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <i x-bind:class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="auth-error" />
                    </div>

                    <div class="mt-5">
                        <label for="password_confirmation" class="field-label">Confirmar contraseña</label>
                        <div class="relative mt-2">
                            <i class="fas fa-shield-halved field-icon" aria-hidden="true"></i>
                            <input x-bind:type="showConfirm ? 'text' : 'password'"
                                   id="password_confirmation" class="login-field pl-11 pr-11" type="password"
                                   name="password_confirmation" placeholder="Repite tu contraseña"
                                   required autocomplete="new-password">
                            <button type="button" x-on:click="showConfirm = !showConfirm" class="field-toggle"
                                    :aria-label="showConfirm ? 'Ocultar confirmación' : 'Mostrar confirmación'">
                                <i x-bind:class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
                    </div>

                    <button type="submit" class="btn-login mt-7">
                        <span>Actualizar contraseña</span>
                        <i class="fas fa-check" aria-hidden="true"></i>
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
