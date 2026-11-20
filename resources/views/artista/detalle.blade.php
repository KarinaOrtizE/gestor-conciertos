<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500" aria-label="Ruta">
            <a href="{{ route('artistas.index') }}" class="hover:text-purple-700">Artistas</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $artista->nombre }}</span>
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl px-4 mx-auto space-y-6 sm:px-6 lg:px-8">
            <section class="overflow-hidden bg-white shadow-sm rounded-2xl ring-1 ring-gray-100">
                <div class="h-28 bg-gradient-to-r from-fuchsia-600 via-purple-600 to-indigo-600"></div>
                <div class="flex flex-wrap items-end gap-5 px-6 pb-6 -mt-14 sm:px-8">
                    <x-foto-artista :artista="$artista" class="text-4xl bg-white shadow-lg w-28 h-28 ring-4 ring-white" />
                    <div class="flex-1 min-w-0 pt-16">
                        <h1 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">{{ $artista->nombre }}</h1>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @forelse (array_filter(array_map('trim', explode(',', (string) $artista->generos))) as $genero)
                                <x-genero :genero="$genero" />
                            @empty
                                <span class="text-sm text-gray-500">Géneros: Sin especificar</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="pt-16">
                        @if ($artista->spotify_id)
                            <a href="https://open.spotify.com/artist/{{ $artista->spotify_id }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 px-4 py-2 font-semibold text-white bg-green-600 rounded-full hover:bg-green-500">
                                <x-icono nombre="nota" class="w-4 h-4" /> Abrir en Spotify
                            </a>
                        @else
                            <span class="text-sm text-gray-500">Registrado manualmente</span>
                        @endif
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-5">
                <section class="p-6 bg-white shadow-sm lg:col-span-3 rounded-2xl ring-1 ring-gray-100" aria-labelledby="titulo-eventos">
                    <h3 id="titulo-eventos" class="font-semibold text-gray-900">Eventos de este artista</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($artista->eventos as $evento)
                            <a href="{{ route('eventos.show', $evento) }}" class="flex items-center gap-4 p-3 transition rounded-xl ring-1 ring-gray-100 hover:ring-purple-300 hover:bg-purple-50/50">
                                <x-portada-evento :evento="$evento" class="w-14 rounded-lg h-14 shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $evento->titulo }}</p>
                                    <p class="text-sm text-gray-500">{{ $evento->fecha->format('d/m/Y') }} · {{ $evento->ciudad }}</p>
                                </div>
                                <x-genero :genero="$evento->genero" class="hidden sm:inline-flex" />
                            </a>
                        @empty
                            <p class="text-gray-500">Aún no tiene eventos.</p>
                        @endforelse
                    </div>
                </section>

                @if ($artista->spotify_id)
                    <section class="p-6 bg-white shadow-sm lg:col-span-2 rounded-2xl ring-1 ring-gray-100" aria-labelledby="titulo-spotify">
                        <h3 id="titulo-spotify" class="mb-3 font-semibold text-gray-900">Escúchalo en Spotify</h3>
                        <x-reproductor-spotify :spotify-id="$artista->spotify_id" title="Canciones de {{ $artista->nombre }} en Spotify" />
                    </section>
                @endif
            </div>

            <a href="{{ route('artistas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-purple-700 hover:underline">← Volver al listado</a>
        </div>
    </div>
</x-app-layout>
