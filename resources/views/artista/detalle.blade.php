<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $artista->nombre }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                @if ($artista->imagen_url)
                    <img src="{{ $artista->imagen_url }}" class="object-cover w-40 h-40 mb-4 rounded-lg" alt="">
                @endif
                <p><strong>Géneros:</strong> {{ $artista->generos ?: 'Sin especificar' }}</p>
                <p><strong>Spotify ID:</strong> {{ $artista->spotify_id ?: 'Registrado manualmente' }}</p>

                <h3 class="mt-6 mb-2 font-bold">Eventos de este artista</h3>
                <ul class="pl-5 list-disc">
                    @forelse ($artista->eventos as $evento)
                        <li>{{ $evento->titulo }} — {{ $evento->fecha->format('d/m/Y') }} ({{ $evento->ciudad }})</li>
                    @empty
                        <li class="text-gray-500 list-none">Aún no tiene eventos.</li>
                    @endforelse
                </ul>

                <a href="{{ route('artistas.index') }}" class="inline-block mt-6 text-blue-600 hover:underline">← Volver al listado</a>
            </div>
        </div>
    </div>
</x-app-layout>