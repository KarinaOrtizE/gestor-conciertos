<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestor de Conciertos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white">

    <nav class="flex items-center justify-between px-8 py-4 bg-gray-800">
        <a href="{{ route('landing') }}" class="text-xl font-bold text-purple-400">🎵 Gestor de Conciertos</a>
        <div class="space-x-4">
            @auth
                <a href="{{ route('dashboard') }}" class="hover:text-purple-400">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="hover:text-purple-400">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="px-4 py-2 bg-purple-600 rounded hover:bg-purple-500">Registrarse</a>
            @endauth
        </div>
    </nav>

    <header class="py-24 text-center bg-gradient-to-b from-purple-900 to-gray-900">
        <h1 class="mb-4 text-5xl font-extrabold">Vive la música en vivo</h1>
        <p class="mb-8 text-lg text-gray-300">Descubre y gestiona los próximos conciertos</p>
        <form method="GET" action="{{ route('landing') }}" class="flex justify-center gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Buscar artista o evento..."
                   class="w-80 px-4 py-2 text-gray-900 rounded">
            <button class="px-6 py-2 bg-purple-600 rounded hover:bg-purple-500">Buscar</button>
        </form>
    </header>

    <section class="max-w-6xl px-8 py-12 mx-auto">
        <h2 class="mb-6 text-2xl font-bold">Eventos destacados</h2>
        <div class="grid gap-6 md:grid-cols-3">
            @forelse ($eventos as $evento)
                <div class="overflow-hidden bg-gray-800 rounded-lg">
                    @if ($evento->afiche)
                        <img src="{{ asset('storage/'.$evento->afiche) }}" alt="{{ $evento->titulo }}" class="object-cover w-full h-48">
                    @else
                        <div class="flex items-center justify-center h-48 bg-gray-700 text-gray-400">Sin afiche</div>
                    @endif
                    <div class="p-4">
                        <h3 class="text-lg font-bold">{{ $evento->titulo }}</h3>
                        <p class="text-purple-300">{{ $evento->artista->nombre }}</p>
                        <p class="text-sm text-gray-400">{{ $evento->fecha->format('d/m/Y H:i') }} · {{ $evento->ciudad }}</p>
                        <p class="mt-2 font-semibold">${{ number_format($evento->precio, 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400">Todavía no hay eventos registrados.</p>
            @endforelse
        </div>
    </section>

</body>
</html>