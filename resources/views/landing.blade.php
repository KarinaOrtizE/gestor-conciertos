@extends('layouts.publico')

@section('titulo', 'Próximos conciertos')

@section('contenido')
    <header class="py-24 text-center bg-gradient-to-b from-purple-900 to-gray-900">
        <h1 class="px-4 mb-4 text-4xl font-extrabold sm:text-5xl">Vive la música en vivo</h1>
        <p class="mb-8 text-lg text-gray-300">Descubre y gestiona los próximos conciertos</p>
        <form method="GET" action="{{ route('landing') }}" class="flex justify-center gap-2 px-4">
            <input type="text" name="q" value="{{ $q }}" placeholder="Buscar artista o evento..."
                   class="w-full max-w-xs px-4 py-2 text-gray-900 rounded">
            <button class="px-6 py-2 bg-purple-600 rounded hover:bg-purple-500">Buscar</button>
        </form>
    </header>

    <section class="max-w-6xl px-4 py-12 mx-auto sm:px-8">
        <h2 class="mb-6 text-2xl font-bold">Próximos eventos</h2>
        <div class="grid gap-6 md:grid-cols-3">
            @forelse ($eventos as $evento)
                <div class="overflow-hidden bg-gray-800 rounded-lg">
                    @if ($evento->aficheUrl())
                        <img src="{{ $evento->aficheUrl() }}" alt="{{ $evento->titulo }}" class="object-cover w-full h-48">
                    @else
                        <div class="flex items-center justify-center h-48 bg-gray-700 text-gray-400">Sin afiche</div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-lg font-bold">{{ $evento->titulo }}</h3>
                            @auth
                                <x-boton-favorito :evento="$evento" :marcado="$favoritos->contains($evento->id)" compacto />
                            @endauth
                        </div>
                        <p class="text-purple-300">{{ $evento->artista->nombre }}</p>
                        <p class="text-sm text-gray-400">{{ $evento->fecha->format('d/m/Y H:i') }} · {{ $evento->ciudad }}</p>
                        <p class="mt-2 font-semibold">${{ number_format($evento->precio, 0, ',', '.') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400">
                    {{ $q !== '' ? 'No encontramos próximos eventos para esa búsqueda.' : 'Todavía no hay próximos eventos.' }}
                </p>
            @endforelse
        </div>
    </section>
@endsection
