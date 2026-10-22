<nav x-data="{ open: false }" class="bg-white border-b border-verde-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <x-application-mark class="block h-9 w-auto" />
                        <span class="hidden sm:block">
                            <span class="block font-display font-black text-lg leading-none text-verde-800">El Campus</span>
                            <span class="block text-[10px] uppercase tracking-[0.25em] text-oro-700 font-sans">Periódico Universitario</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Panel') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('perfil') }}" :active="request()->routeIs('perfil')">
                        {{ __('Mi perfil') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('noticias.index') }}" :active="request()->routeIs('noticias.*')">
                        {{ __('Noticias') }}
                    </x-nav-link>
                    @if (Auth::user()->isAdministrador())
                        <x-nav-link href="{{ route('autores.index') }}" :active="request()->routeIs('autores.*')">
                            {{ __('Autores') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('categorias.index') }}" :active="request()->routeIs('categorias.*')">
                            {{ __('Categorías') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('usuarios.index') }}" :active="request()->routeIs('usuarios.*')">
                            {{ __('Usuarios') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link href="{{ route('eventos.index') }}" :active="request()->routeIs('eventos.*')">
                        {{ __('Eventos') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('inicio') }}" :active="request()->routeIs('inicio')">
                        {{ __('Ver el periódico') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Settings Dropdown -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <span class="inline-flex rounded-md">
                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-700 bg-white hover:text-verde-700 focus:outline-none focus:bg-neutral-50 active:bg-neutral-50 transition ease-in-out duration-150">
                                    {{ Auth::user()->name }}

                                    <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </span>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-neutral-400">
                                {{ __('Mi cuenta') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Configuración de mi perfil') }}
                            </x-dropdown-link>

                            <div class="border-t border-neutral-200"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('Cerrar sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" :aria-expanded="open ? 'true' : 'false'" aria-controls="menu-movil" aria-label="Abrir o cerrar el menú de navegación" class="inline-flex items-center justify-center p-2 rounded-md text-neutral-400 hover:text-neutral-500 hover:bg-neutral-100 focus:outline-none focus:bg-neutral-100 focus:text-neutral-500 transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div id="menu-movil" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Panel') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('perfil') }}" :active="request()->routeIs('perfil')">
                {{ __('Mi perfil') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('noticias.index') }}" :active="request()->routeIs('noticias.*')">
                {{ __('Noticias') }}
            </x-responsive-nav-link>
            @if (Auth::user()->isAdministrador())
                <x-responsive-nav-link href="{{ route('autores.index') }}" :active="request()->routeIs('autores.*')">
                    {{ __('Autores') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('categorias.index') }}" :active="request()->routeIs('categorias.*')">
                    {{ __('Categorías') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('usuarios.index') }}" :active="request()->routeIs('usuarios.*')">
                    {{ __('Usuarios') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link href="{{ route('eventos.index') }}" :active="request()->routeIs('eventos.*')">
                {{ __('Eventos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('inicio') }}" :active="request()->routeIs('inicio')">
                {{ __('Ver el periódico') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-neutral-200">
            <div class="flex items-center px-4">
                <div>
                    <div class="font-medium text-base text-neutral-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-neutral-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Configuración de mi perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>