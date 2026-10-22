<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="font-display text-2xl font-bold text-verde-800 text-center">¿Olvidaste tu contraseña?</h1>
        <div class="mt-2 mb-4 text-sm text-neutral-600">
            {{ __('No hay problema. Escríbenos tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.') }}
        </div>

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-700">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Correo electrónico') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Enviar enlace de restablecimiento') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>