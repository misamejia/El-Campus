<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-display text-2xl font-bold text-neutral-900 leading-tight">
                {{ __('Gestión de usuarios') }}
            </h2>
                <p class="mt-1 font-sans text-sm text-neutral-600">
                {{ __('Administra el rol de cada usuario del sistema.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('exito'))
                <x-alerta tipo="exito" class="mb-6">{{ session('exito') }}</x-alerta>
            @endif

            @if ($errors->any())
                <x-alerta tipo="error" class="mb-6">{{ $errors->first() }}</x-alerta>
            @endif

            <div class="bg-white overflow-hidden shadow-sm border border-neutral-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-papel">
                            <tr class="font-sans text-xs uppercase tracking-widest text-neutral-600">
                                <th class="px-4 py-3 text-start">Nombre</th>
                                <th class="px-4 py-3 text-start">Correo</th>
                                <th class="px-4 py-3 text-start">Verificación</th>
                                <th class="px-4 py-3 text-center">Rol actual</th>
                                <th class="px-4 py-3 text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse ($usuarios as $usuario)
                                <tr class="hover:bg-papel transition-colors">
                                    <td class="px-4 py-4 font-serif text-sm font-semibold text-neutral-800">
                                        {{ $usuario->name }}
                                        @if ($usuario->is(Auth::user()))
                                            <span class="ms-1.5 font-sans text-xs font-semibold text-verde-700">(tú)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-neutral-600 break-all">
                                        {{ $usuario->email }}
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($usuario->email_verified_at)
                                            <span class="inline-block rounded bg-green-100 px-2 py-0.5 font-sans text-xs font-semibold text-green-800">Verificado</span>
                                        @else
                                            <span class="inline-block rounded bg-neutral-200 px-2 py-0.5 font-sans text-xs font-semibold text-neutral-700">No verificado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if ($usuario->isAdministrador())
                                            <span class="inline-block rounded bg-oro-100 px-2 py-0.5 font-sans text-xs font-semibold text-oro-900">Administrador</span>
                                        @elseif ($usuario->isEditor())
                                            <span class="inline-block rounded bg-verde-50 px-2 py-0.5 font-sans text-xs font-semibold text-verde-800">Editor</span>
                                        @else
                                            <span class="inline-block rounded bg-neutral-100 px-2 py-0.5 font-sans text-xs font-semibold text-neutral-700">Lector</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-3 text-sm">
                                            @if ($usuario->is(Auth::user()))
                                                <span class="font-sans text-xs text-neutral-600">No puedes cambiar tu propio rol</span>
                                            @elseif ($usuario->isAdministrador() && $totalAdministradores === 1)
                                                <span class="font-sans text-xs text-neutral-400">Último administrador</span>
                                            @else
                                                <form method="POST" action="{{ route('usuarios.rol.actualizar', $usuario) }}" class="flex items-center justify-end gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label for="rol-{{ $usuario->id }}" class="sr-only">Rol de {{ $usuario->name }}</label>
                                                    <select id="rol-{{ $usuario->id }}" name="role" class="rounded-md border-neutral-300 font-sans text-sm text-neutral-700 shadow-sm focus:border-verde-500 focus:ring-verde-500">
                                                        <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected($usuario->isAdministrador())>Administrador</option>
                                                        <option value="{{ \App\Models\User::ROLE_EDITOR }}" @selected($usuario->isEditor())>Editor</option>
                                                        <option value="{{ \App\Models\User::ROLE_LECTOR }}" @selected($usuario->isLector())>Lector</option>
                                                    </select>
                                                    <button type="submit" class="font-semibold text-verde-700 hover:text-verde-500">Actualizar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center font-sans text-sm text-neutral-600">
                                        No hay usuarios registrados todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

                <p class="mt-6 text-sm text-neutral-600">
                Se muestran {{ $usuarios->count() }} usuario(s).
            </p>

            <div class="mt-4">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
</x-app-layout>