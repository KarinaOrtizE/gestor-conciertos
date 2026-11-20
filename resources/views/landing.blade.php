@extends('layouts.publico')

@section('titulo', 'Próximos conciertos')

@section('contenido')
    <header class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-700 via-purple-800 to-indigo-950"></div>
        <div class="absolute rounded-full -top-32 -left-24 w-96 h-96 bg-pink-500/40 blur-3xl"></div>
        <div class="absolute rounded-full top-16 -right-24 w-[28rem] h-[28rem] bg-amber-400/20 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-gray-950"></div>

        <div class="relative max-w-5xl px-4 pt-20 pb-24 mx-auto text-center sm:pt-28">
            <p class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium rounded-full text-fuchsia-100 bg-white/10 ring-1 ring-white/20">
                <x-icono nombre="nota" class="w-4 h-4" /> Conciertos en vivo en Colombia
            </p>
            <h1 class="mt-6 text-4xl font-extrabold tracking-tight sm:text-6xl">
                Vive la música
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-300 via-amber-200 to-yellow-300">en vivo</span>
            </h1>
            <p class="max-w-2xl mx-auto mt-5 text-lg text-purple-100">
                Descubre los próximos conciertos, guarda tus favoritos y escucha a los artistas antes del show.
            </p>

            <form method="GET" action="{{ route('landing') }}"
                  class="flex items-center max-w-xl gap-1 p-1.5 mx-auto mt-10 bg-white rounded-full shadow-2xl shadow-purple-950/50">
                <label for="q" class="sr-only">Buscar artista o evento</label>
                <x-icono nombre="buscar" class="w-5 h-5 ml-3 text-gray-400" />
                <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Buscar artista o evento..."
                       class="flex-1 min-w-0 px-2 py-2 text-gray-900 bg-transparent border-0 focus:ring-0">
                @if ($genero)
                    <input type="hidden" name="genero" value="{{ $genero }}">
                @endif
                <button class="px-5 py-2.5 font-semibold text-white rounded-full sm:px-7 bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-500 hover:to-purple-500">
                    Buscar
                </button>
            </form>

            <dl class="grid max-w-lg grid-cols-3 gap-4 mx-auto mt-12">
                @foreach ([['conciertos', 'Próximos conciertos'], ['artistas', 'Artistas'], ['ciudades', 'Ciudades']] as [$clave, $etiqueta])
                    <div class="flex flex-col-reverse">
                        <dt class="mt-1 text-sm text-purple-200">{{ $etiqueta }}</dt>
                        <dd class="text-3xl font-extrabold sm:text-4xl">{{ $cifras[$clave] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </header>

    <section class="max-w-6xl px-4 pb-16 mx-auto sm:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold sm:text-3xl">Próximos eventos</h2>

            @if ($generos->count() > 1)
                <nav class="flex flex-wrap gap-2" aria-label="Filtrar por género">
                    @foreach ($generos->prepend(null) as $opcion)
                        <a href="{{ route('landing', array_filter(['q' => $q, 'genero' => $opcion])) }}"
                           @if ($opcion === $genero) aria-current="true" @endif
                           @class([
                               'px-3 py-1 text-sm font-medium rounded-full ring-1 transition',
                               'bg-white text-gray-900 ring-white' => $opcion === $genero,
                               'text-gray-300 ring-white/20 hover:bg-white/10' => $opcion !== $genero,
                           ])>{{ $opcion ?? 'Todos' }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($eventos as $evento)
                <article class="relative flex flex-col overflow-hidden transition duration-300 bg-gray-900 group rounded-2xl ring-1 ring-white/10 hover:-translate-y-1 hover:ring-fuchsia-400/50 hover:shadow-2xl hover:shadow-fuchsia-950/60">
                    <div class="relative overflow-hidden h-52">
                        <x-portada-evento :evento="$evento" class="w-full h-full transition duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/10 to-transparent"></div>
                        <x-fecha-evento :fecha="$evento->fecha" class="absolute top-3 left-3" />
                        @auth
                            <div class="absolute rounded-full top-3 right-3 bg-gray-950/60 backdrop-blur">
                                <x-boton-favorito :evento="$evento" :marcado="$favoritos->contains($evento->id)" compacto class="p-1" />
                            </div>
                        @endauth
                        <x-genero :genero="$evento->genero" class="absolute bottom-3 left-3" />
                    </div>

                    <div class="flex flex-col flex-1 p-5">
                        <h3 class="text-lg font-bold leading-snug">{{ $evento->titulo }}</h3>
                        <div class="flex items-center gap-2 mt-2">
                            <x-foto-artista :artista="$evento->artista" class="text-xs w-7 h-7" />
                            <span class="font-medium text-purple-300">{{ $evento->artista->nombre }}</span>
                        </div>

                        <ul class="mt-4 space-y-1.5 text-sm text-gray-400">
                            <li class="flex items-center gap-2"><x-icono nombre="ubicacion" class="w-4 h-4 text-fuchsia-400" /> {{ $evento->lugar }} · {{ $evento->ciudad }}</li>
                            <li class="flex items-center gap-2"><x-icono nombre="reloj" class="w-4 h-4 text-fuchsia-400" /> {{ $evento->fecha->locale(app()->getLocale())->isoFormat('dddd D [de] MMMM, HH:mm') }}</li>
                        </ul>

                        <div class="flex items-center justify-between pt-4 mt-auto border-t border-white/10">
                            <span class="text-xl font-extrabold">${{ number_format($evento->precio, 0, ',', '.') }}</span>
                            @auth
                                <a href="{{ route('eventos.show', $evento) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-fuchsia-300 hover:text-fuchsia-200">
                                    Ver detalle <x-icono nombre="flecha" class="w-4 h-4" />
                                </a>
                            @endauth
                        </div>
                    </div>
                </article>
            @empty
                <p class="py-12 text-center text-gray-400 sm:col-span-2 lg:col-span-3">
                    {{ $q !== '' || $genero ? 'No encontramos próximos eventos para esa búsqueda.' : 'Todavía no hay próximos eventos.' }}
                </p>
            @endforelse
        </div>
    </section>

    @if ($artistas->isNotEmpty())
        <section class="py-16 border-t bg-gray-900/60 border-white/5">
            <div class="max-w-6xl px-4 mx-auto sm:px-8">
                <h2 class="text-2xl font-bold sm:text-3xl">Artistas en cartelera</h2>
                <p class="mt-1 text-gray-400">Toca un artista para ver sus conciertos.</p>

                <div class="flex flex-wrap justify-center gap-x-10 gap-y-8 mt-8">
                    @foreach ($artistas as $artista)
                        <a href="{{ route('landing', ['q' => $artista->nombre]) }}" class="w-32 text-center group">
                            <x-foto-artista :artista="$artista" class="w-24 h-24 mx-auto text-3xl transition ring-4 ring-white/10 group-hover:ring-fuchsia-400/70 group-hover:scale-105" />
                            <p class="mt-3 font-semibold group-hover:text-fuchsia-300">{{ $artista->nombre }}</p>
                            <p class="text-sm text-gray-400">{{ $artista->proximos_count }} {{ $artista->proximos_count === 1 ? 'concierto' : 'conciertos' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @guest
        <section class="max-w-6xl px-4 py-16 mx-auto sm:px-8">
            <div class="relative px-6 py-12 overflow-hidden text-center sm:px-12 rounded-3xl bg-gradient-to-r from-fuchsia-600 via-purple-600 to-indigo-600">
                <div class="absolute rounded-full -bottom-24 -right-10 w-72 h-72 bg-white/10 blur-2xl"></div>
                <h2 class="relative text-2xl font-extrabold sm:text-3xl">Guarda tus conciertos favoritos</h2>
                <p class="relative max-w-xl mx-auto mt-3 text-purple-100">Crea tu cuenta gratis para marcar eventos, ver el clima del día del concierto y escuchar a los artistas.</p>
                <div class="relative flex flex-wrap justify-center gap-3 mt-8">
                    <a href="{{ route('register') }}" class="px-6 py-3 font-semibold text-purple-700 bg-white rounded-full hover:bg-purple-50">Crear cuenta</a>
                    <a href="{{ route('login') }}" class="px-6 py-3 font-semibold text-white rounded-full ring-1 ring-white/50 hover:bg-white/10">Iniciar sesión</a>
                </div>
            </div>
        </section>
    @endguest
@endsection
