<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($titulo) ? $titulo.' · ' : '' }}{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900">
        <div class="flex min-h-screen">
            {{-- Panel de marca: solo en pantallas medianas o más grandes --}}
            <aside class="relative flex-col justify-between hidden w-5/12 p-10 overflow-hidden text-white md:flex bg-gradient-to-br from-purple-900 via-purple-800 to-gray-900">
                <a href="{{ route('landing') }}" class="text-2xl font-bold text-purple-200">🎵 {{ config('app.name') }}</a>

                <div>
                    <p class="text-4xl font-extrabold leading-tight">Vive la música<br>en vivo</p>
                    <ul class="mt-6 space-y-3 text-purple-100">
                        <li class="flex items-center gap-3"><x-icono nombre="calendario" /> Próximos conciertos con su afiche y el clima del día</li>
                        <li class="flex items-center gap-3"><x-icono nombre="corazon" /> Guarda tus eventos favoritos</li>
                        <li class="flex items-center gap-3"><x-icono nombre="microfono" /> Escucha a los artistas en Spotify</li>
                    </ul>
                </div>

                <p class="text-sm text-purple-200">Conciertos en vivo en Colombia</p>
            </aside>

            <main class="flex flex-col items-center justify-center flex-1 px-4 py-10 bg-gray-50 sm:px-8">
                <a href="{{ route('landing') }}" class="mb-8 text-2xl font-bold text-purple-700 md:hidden">🎵 {{ config('app.name') }}</a>

                <div class="w-full max-w-md">
                    @isset($titulo)
                        <h1 class="text-2xl font-bold text-gray-900">{{ $titulo }}</h1>
                    @endisset
                    @isset($subtitulo)
                        <p class="mt-1 text-gray-600">{{ $subtitulo }}</p>
                    @endisset

                    <div class="p-6 mt-6 bg-white border border-gray-100 shadow-sm rounded-xl sm:p-8">
                        {{ $slot }}
                    </div>

                    @isset($pie)
                        <div class="mt-6 text-sm text-center text-gray-600">{{ $pie }}</div>
                    @endisset
                </div>
            </main>
        </div>
    </body>
</html>
