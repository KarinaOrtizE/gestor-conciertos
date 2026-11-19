<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $evento->titulo }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if ($evento->aficheUrl())
                    <img src="{{ $evento->aficheUrl() }}" class="object-cover w-64 mb-4 rounded-lg" alt="Afiche">
                @endif
                <p><strong>Artista:</strong>
                    <a href="{{ route('artistas.show', $evento->artista) }}" class="text-blue-600 hover:underline">{{ $evento->artista->nombre }}</a>
                </p>
                <p><strong>Fecha:</strong> {{ $evento->fecha->format('d/m/Y H:i') }}</p>
                <p><strong>Lugar:</strong> {{ $evento->lugar }}, {{ $evento->ciudad }}</p>
                <p><strong>Género:</strong> {{ $evento->genero }}</p>
                <p><strong>Precio:</strong> ${{ number_format($evento->precio, 0, ',', '.') }}</p>

                @if ($evento->artista->spotify_id)
                    <section class="mt-6" aria-labelledby="titulo-reproductor">
                        <h3 id="titulo-reproductor" class="mb-2 text-sm font-semibold text-gray-900">Escucha a {{ $evento->artista->nombre }}</h3>
                        <x-reproductor-spotify :spotify-id="$evento->artista->spotify_id" compacto title="Canciones de {{ $evento->artista->nombre }} en Spotify" />
                    </section>
                @endif

                <section class="p-4 mt-6 border rounded-lg bg-sky-50 border-sky-100" aria-labelledby="titulo-clima">
                    <h3 id="titulo-clima" class="text-sm font-semibold text-sky-900">Clima para el día del evento</h3>

                    @if ($clima['estado'] === 'ok')
                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-4xl" aria-hidden="true">{{ $clima['icono'] }}</span>
                            <div>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ round($clima['temperatura']) }} °C
                                    <span class="text-base font-normal text-gray-600">a las {{ $evento->fecha->format('H:i') }}</span>
                                </p>
                                <p class="text-gray-700">{{ $clima['descripcion'] }}</p>
                                <p class="text-sm text-gray-600">
                                    Mín. {{ round($clima['minima']) }} °C · Máx. {{ round($clima['maxima']) }} °C
                                    @if (! is_null($clima['lluvia']))
                                        · Probabilidad de lluvia {{ $clima['lluvia'] }} %
                                    @endif
                                </p>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Pronóstico para {{ $clima['ciudad'] }} · Fuente: Open-Meteo</p>
                    @else
                        <p class="mt-1 text-sm text-gray-600">{{ $clima['mensaje'] }}</p>
                    @endif
                </section>

                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <a href="{{ route('eventos.index') }}" class="text-blue-600 hover:underline">← Volver al listado</a>
                    <x-boton-favorito :evento="$evento" :marcado="$esFavorito" />
                    <a href="{{ route('eventos.pdf', $evento) }}"
                       class="px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50">Descargar ficha en PDF</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>