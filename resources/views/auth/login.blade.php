<x-guest-layout>
    <x-slot name="titulo">Inicia sesión</x-slot>
    <x-slot name="subtitulo">Entra para gestionar artistas, eventos y tus favoritos.</x-slot>
    <x-slot name="pie">
        ¿No tienes cuenta? <a href="{{ route('register') }}" class="font-medium text-purple-700 hover:text-purple-900 hover:underline">Regístrate</a>
    </x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-6">
            @if (Route::has('password.request'))
                <a class="text-purple-700 hover:text-purple-900 hover:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <div class="p-3 mt-6 text-sm text-gray-600 rounded-lg bg-purple-50">
        <p class="font-medium text-purple-900">Cuentas de prueba</p>
        <p>admin@example.com (administrador) o test@example.com (usuario), contraseña <code>password</code>.</p>
    </div>
</x-guest-layout>
