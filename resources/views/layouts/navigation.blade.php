{{-- Fondo oscuro detrás del menú cuando se abre en pantallas pequeñas --}}
<div x-show="menuAbierto" x-transition.opacity x-cloak @click="menuAbierto = false"
     class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

<aside id="menu-lateral"
       :class="menuAbierto ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 text-gray-300 transition-transform duration-200 -translate-x-full bg-gray-900 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0">

    <div class="flex items-center justify-between h-16 px-5 border-b border-gray-800">
        <a href="{{ route('dashboard') }}" class="text-base font-bold text-purple-400 whitespace-nowrap lg:text-lg">🎵 Gestor de Conciertos</a>
        <button type="button" @click="menuAbierto = false" class="text-gray-400 hover:text-white lg:hidden" aria-label="Cerrar menú">
            <x-icono nombre="cerrar" class="w-6 h-6" />
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icono="inicio">
            {{ __('Dashboard') }}
        </x-sidebar-link>
        <x-sidebar-link :href="route('artistas.index')" :active="request()->routeIs('artistas.*')" icono="microfono">
            Artistas
        </x-sidebar-link>
        <x-sidebar-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')" icono="calendario">
            Eventos
        </x-sidebar-link>
        <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" icono="usuario">
            {{ __('Profile') }}
        </x-sidebar-link>

        <div class="pt-4 mt-4 border-t border-gray-800">
            <x-sidebar-link :href="route('landing')" icono="mundo">
                Ver sitio público
            </x-sidebar-link>
        </div>
    </nav>

    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center gap-3 mb-3">
            <x-avatar :user="Auth::user()" />
            <div class="min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400">{{ Auth::user()->esAdmin() ? 'Administrador' : 'Usuario' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center w-full gap-3 px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-gray-800 hover:text-white">
                <x-icono nombre="salir" />
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</aside>
