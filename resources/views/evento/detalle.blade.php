<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $evento->titulo }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if ($evento->afiche)
                    <img src="{{ asset('storage/'.$evento->afiche) }}" class="object-cover w-64 mb-4 rounded-lg" alt="Afiche">
                @endif
                <p><strong>Artista:</strong>
                    <a href="{{ route('artistas.show', $evento->artista) }}" class="text-blue-600 hover:underline">{{ $evento->artista->nombre }}</a>
                </p>
                <p><strong>Fecha:</strong> {{ $evento->fecha->format('d/m/Y H:i') }}</p>
                <p><strong>Lugar:</strong> {{ $evento->lugar }}, {{ $evento->ciudad }}</p>
                <p><strong>Género:</strong> {{ $evento->genero }}</p>
                <p><strong>Precio:</strong> ${{ number_format($evento->precio, 0, ',', '.') }}</p>

                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <a href="{{ route('eventos.index') }}" class="text-blue-600 hover:underline">← Volver al listado</a>
                    <a href="{{ route('eventos.pdf', $evento) }}"
                       class="px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded hover:bg-gray-50">Descargar ficha en PDF</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>