<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('titulo')@yield('titulo') · @endif{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col min-h-screen font-sans antialiased text-white bg-gray-950">

    <nav class="sticky top-0 z-30 flex flex-wrap items-center justify-between gap-3 px-4 py-4 border-b sm:px-8 bg-gray-950/80 backdrop-blur border-white/10">
        <a href="{{ route('landing') }}" class="text-xl font-bold text-purple-400">🎵 {{ config('app.name') }}</a>
        <div class="flex items-center gap-4">
            @auth
                <a href="{{ route('dashboard') }}" class="hover:text-purple-400">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="hover:text-purple-400">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="px-4 py-2 font-semibold rounded-full bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-500 hover:to-purple-500">Registrarse</a>
            @endauth
        </div>
    </nav>

    <main class="flex-1">
        @yield('contenido')
    </main>

    <footer class="px-4 py-8 text-sm text-center text-gray-500 border-t border-white/10">
        © {{ date('Y') }} {{ config('app.name') }} · Conciertos en vivo en Colombia
    </footer>
</body>
</html>
