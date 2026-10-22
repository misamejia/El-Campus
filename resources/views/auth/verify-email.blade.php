<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="font-display text-2xl font-bold text-verde-800 text-center">Verifica tu correo electrónico</h1>
        <div class="mt-2 mb-4 text-sm text-neutral-600">
            {{ __('Antes de continuar, verifica tu dirección de correo electrónico haciendo clic en el enlace que te enviamos. Si no lo recibiste, con gusto te enviaremos otro.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-700">
                {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que registraste.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-button type="submit">
                        {{ __('Reenviar correo de verificación') }}
                    </x-button>
                </div>
            </form>

            <div>
                <a
                    href="{{ route('profile.show') }}"
                    class="underline text-sm text-neutral-600 hover:text-verde-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-verde-500"
                >
                    {{ __('Editar perfil') }}</a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf

                    <button type="submit" class="underline text-sm text-neutral-600 hover:text-verde-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-verde-500 ms-2">
                        {{ __('Cerrar sesión') }}
                    </button>
                </form>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>