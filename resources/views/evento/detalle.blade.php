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

                <a href="{{ route('eventos.index') }}" class="inline-block mt-6 text-blue-600 hover:underline">← Volver al listado</a>
            </div>
        </div>
    </div>
</x-app-layout>