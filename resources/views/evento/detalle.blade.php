<x-app-layout>
    <x-slot name="header">
        <nav class="text-sm text-gray-500" aria-label="Ruta">
            <a href="{{ route('eventos.index') }}" class="hover:text-purple-700">Eventos</a>
            <span class="mx-1">/</span>
            <span class="text-gray-700">{{ $evento->titulo }}</span>
        </nav>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl px-4 mx-auto space-y-6 sm:px-6 lg:px-8">
            <article class="overflow-hidden bg-white shadow-sm rounded-2xl ring-1 ring-gray-100 md:flex">
                <div class="relative md:w-2/5 shrink-0">
                    <x-portada-evento :evento="$evento" class="w-full h-64 md:h-full md:min-h-[22rem]" />
                    <x-fecha-evento :fecha="$evento->fecha" class="absolute top-4 left-4" />
                </div>

                <div class="flex flex-col flex-1 p-6 sm:p-8">
                    <x-genero :genero="$evento->genero" class="self-start" />
                    <h1 class="mt-3 text-2xl font-extrabold text-gray-900 sm:text-3xl">{{ $evento->titulo }}</h1>

                    <a href="{{ route('artistas.show', $evento->artista) }}" class="inline-flex items-center self-start gap-3 mt-4 group">
                        <x-foto-artista :artista="$evento->artista" class="w-10 h-10 text-sm" />
                        <span>
                            <span class="block text-xs text-gray-500">Artista</span>
                            <span class="font-semibold text-purple-700 group-hover:underline">{{ $evento->artista->nombre }}</span>
                        </span>
                    </a>

                    <dl class="grid gap-4 mt-6 sm:grid-cols-2">
                        @foreach ([
                            ['calendario', 'Fecha', ucfirst($evento->fecha->locale(app()->getLocale())->isoFormat('dddd D [de] MMMM [de] YYYY'))],
                            ['reloj', 'Hora', $evento->fecha->format('H:i')],
                            ['ubicacion', 'Lugar', $evento->lugar.', '.$evento->ciudad],
                            ['ticket', 'Precio', '$'.number_format($evento->precio, 0, ',', '.')],
                        ] as [$icono, $etiqueta, $valor])
                            <div class="flex items-start gap-3">
                                <span class="flex items-center justify-center w-10 h-10 text-purple-600 rounded-xl bg-purple-50 shrink-0">
                                    <x-icono :nombre="$icono" />
                                </span>
                                <div>
                                    <dt class="text-xs text-gray-500">{{ $etiqueta }}</dt>
                                    <dd class="font-semibold text-gray-900">{{ $valor }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>

                    <div class="flex flex-wrap items-center gap-3 pt-6 mt-auto">
                        <x-boton-favorito :evento="$evento" :marcado="$esFavorito" />
                        <a href="{{ route('eventos.pdf', $evento) }}"
                           class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                            <x-icono nombre="descargar" class="w-4 h-4" /> Descargar ficha en PDF
                        </a>
                        @php
                            $textoWhatsapp = "\u{1F3B5} *{$evento->titulo}* con {$evento->artista->nombre}\n"
                                . "\u{1F4C5} {$evento->fecha->format('d/m/Y H:i')}\n"
                                . "\u{1F4CD} {$evento->lugar}, {$evento->ciudad}\n\n"
                                . "Mira más conciertos aquí: " . route('landing');
                        @endphp
                        <a href="https://wa.me/?text={{ rawurlencode($textoWhatsapp) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-3 py-2 text-sm text-white bg-green-600 border border-green-600 rounded hover:bg-green-500">
                            <x-icono nombre="compartir" class="w-4 h-4" /> Compartir por WhatsApp
                        </a>
                        <a href="{{ route('eventos.ics', $evento) }}"
                           class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                            <x-icono nombre="calendario" class="w-4 h-4" /> Agregar a mi calendario
                        </a>
                        @can('administrar')
                            <a href="{{ route('eventos.edit', $evento) }}"
                               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                                <x-icono nombre="lapiz" class="w-4 h-4" /> Editar
                            </a>
                        @endcan
                    </div>
                </div>
            </article>

            <div @class(['grid gap-6', 'lg:grid-cols-2' => $evento->artista->spotify_id])>
                <section class="p-6 text-white shadow-sm rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600" aria-labelledby="titulo-clima">
                    <h3 id="titulo-clima" class="text-sm font-semibold text-sky-100">Clima para el día del evento</h3>

                    @if ($clima['estado'] === 'ok')
                        <div class="flex items-center gap-5 mt-4">
                            <span class="text-6xl drop-shadow" aria-hidden="true">{{ $clima['icono'] }}</span>
                            <div>
                                <p class="text-4xl font-extrabold">
                                    {{ round($clima['temperatura']) }} °C
                                    <span class="text-base font-normal text-sky-100">a las {{ $evento->fecha->format('H:i') }}</span>
                                </p>
                                <p class="text-lg">{{ $clima['descripcion'] }}</p>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-sky-50">
                            Mín. {{ round($clima['minima']) }} °C · Máx. {{ round($clima['maxima']) }} °C
                            @if (! is_null($clima['lluvia']))
                                · Probabilidad de lluvia {{ $clima['lluvia'] }} %
                            @endif
                        </p>
                        <p class="mt-3 text-xs text-sky-100">Pronóstico para {{ $clima['ciudad'] }} · Fuente: {{ $clima['fuente'] }}</p>
                    @else
                        <p class="mt-3 text-sky-50">{{ $clima['mensaje'] }}</p>
                    @endif
                </section>

                @if ($evento->artista->spotify_id)
                    <section class="p-6 bg-white shadow-sm rounded-2xl ring-1 ring-gray-100" aria-labelledby="titulo-reproductor">
                        <h3 id="titulo-reproductor" class="mb-3 text-sm font-semibold text-gray-900">Escucha a {{ $evento->artista->nombre }}</h3>
                        <x-reproductor-spotify :spotify-id="$evento->artista->spotify_id" compacto title="Canciones de {{ $evento->artista->nombre }} en Spotify" />
                    </section>
                @endif
            </div>

            <a href="{{ route('eventos.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-purple-700 hover:underline">← Volver al listado</a>
        </div>
    </div>
</x-app-layout>
