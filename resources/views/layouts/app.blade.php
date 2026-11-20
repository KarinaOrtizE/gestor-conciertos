<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-slate-50">
        <div x-data="{ menuAbierto: false }" @keydown.escape.window="menuAbierto = false" class="min-h-screen lg:flex">
            @include('layouts.navigation')

            <div class="flex flex-col flex-1 min-w-0">
                <!-- Barra superior solo en pantallas pequeñas -->
                <div class="flex items-center gap-3 px-4 text-white bg-gray-900 h-14 lg:hidden">
                    <button type="button" @click="menuAbierto = true" class="text-gray-300 hover:text-white"
                            aria-label="Abrir menú" aria-controls="menu-lateral" :aria-expanded="menuAbierto">
                        <x-icono nombre="menu" class="w-6 h-6" />
                    </button>
                    <span class="font-bold text-purple-400">🎵 Gestor de Conciertos</span>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-gray-100">
                        <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
