<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Mis favoritos</h2>
    </x-slot>

    @php
        $secciones = [
            ['Próximos', $proximos, 'No tienes próximos eventos en favoritos.'],
            ['Ya pasaron', $pasados, null],
        ];
    @endphp

    <div class="py-8">
        <div class="px-4 mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8">
            @if ($proximos->isEmpty() && $pasados->isEmpty())
                <div class="p-8 text-center bg-white rounded-lg shadow-sm">
                    <p class="text-gray-700">Aún no tienes eventos favoritos.</p>
                    <p class="mt-1 text-sm text-gray-500">Márcalos con el corazón en el listado o en el detalle de cada evento.</p>
                    <a href="{{ route('eventos.index') }}" class="inline-block px-4 py-2 mt-4 text-white bg-purple-600 rounded hover:bg-purple-500">Ver eventos</a>
                </div>
            @else
                @foreach ($secciones as [$titulo, $eventos, $vacio])
                    @if ($eventos->isNotEmpty() || $vacio)
                        <section>
                            <h3 class="mb-3 font-semibold text-gray-900">{{ $titulo }}</h3>

                            @if ($eventos->isEmpty())
                                <p class="text-sm text-gray-500">{{ $vacio }}</p>
                            @else
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($eventos as $evento)
                                        <article class="flex gap-4 p-4 bg-white rounded-lg shadow-sm">
                                            @if ($evento->afiche)
                                                <img src="{{ asset('storage/'.$evento->afiche) }}" alt="" class="object-cover w-16 h-20 rounded shrink-0">
                                            @else
                                                <div class="flex items-center justify-center w-16 h-20 text-gray-400 bg-gray-100 rounded shrink-0">
                                                    <x-icono nombre="calendario" class="w-6 h-6" />
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <a href="{{ route('eventos.show', $evento) }}" class="font-medium text-gray-900 hover:text-purple-700">{{ $evento->titulo }}</a>
                                                    <x-boton-favorito :evento="$evento" marcado compacto />
                                                </div>
                                                <p class="text-sm text-purple-700">{{ $evento->artista->nombre }}</p>
                                                <p class="text-sm text-gray-500">{{ $evento->fecha->format('d/m/Y H:i') }} · {{ $evento->ciudad }}</p>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
