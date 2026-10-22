<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="font-display text-2xl font-bold text-verde-800 text-center mb-4">Zona segura</h1>

        <div class="mb-4 text-sm text-neutral-600">
            {{ __('Esta es una zona segura de la aplicación. Confirma tu contraseña antes de continuar.') }}
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div>
                <x-label for="password" value="{{ __('Contraseña') }}" />
                <x-password-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" autofocus />
            </div>

            <div class="flex justify-end mt-4">
                <x-button class="ms-4">
                    {{ __('Confirmar') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>