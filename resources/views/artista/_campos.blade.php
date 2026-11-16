@inject('spotify', 'App\Services\SpotifyService')

@if ($spotify->configurado())
    <div x-data="buscadorSpotify('{{ route('artistas.spotify') }}')" class="p-4 border border-green-200 rounded-lg bg-green-50">
        <x-input-label for="busqueda-spotify" value="Buscar en Spotify para llenar los datos" />
        <div class="flex gap-2 mt-1">
            <input id="busqueda-spotify" type="search" x-model="q" @keydown.enter.prevent="buscar()"
                   placeholder="Nombre del artista" class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500">
            <button type="button" @click="buscar()" :disabled="cargando || q.trim().length < 2"
                    class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-500 disabled:opacity-50">
                <span x-text="cargando ? 'Buscando…' : 'Buscar'"></span>
            </button>
        </div>

        <p x-show="error" x-text="error" x-cloak class="mt-2 text-sm text-red-600"></p>
        <p x-show="buscado && !cargando && !error && resultados.length === 0" x-cloak class="mt-2 text-sm text-gray-600">
            No se encontraron artistas con ese nombre.
        </p>

        <ul x-show="resultados.length" x-cloak class="mt-3 space-y-2">
            <template x-for="artista in resultados" :key="artista.spotify_id">
                <li class="flex items-center gap-3 p-2 bg-white rounded-md">
                    <img x-show="artista.imagen_url" :src="artista.imagen_url" alt="" class="object-cover w-10 h-10 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate" x-text="artista.nombre"></p>
                        <p class="text-xs text-gray-500 truncate" x-text="artista.generos || 'Sin géneros'"></p>
                    </div>
                    <button type="button" @click="usar(artista)" class="px-3 py-1 text-sm text-green-700 border border-green-600 rounded hover:bg-green-50">
                        Usar
                    </button>
                </li>
            </template>
        </ul>
    </div>
@endif

<div>
    <x-input-label for="nombre" value="Nombre del artista" />
    <x-text-input id="nombre" name="nombre" type="text" class="block w-full mt-1"
                  :value="old('nombre', $artista->nombre ?? '')" required />
    <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
</div>

<div>
    <x-input-label for="generos" value="Géneros (separados por coma)" />
    <x-text-input id="generos" name="generos" type="text" class="block w-full mt-1"
                  :value="old('generos', $artista->generos ?? '')" />
    <x-input-error :messages="$errors->get('generos')" class="mt-2" />
</div>

<div>
    <x-input-label for="imagen_url" value="URL de la imagen" />
    <x-text-input id="imagen_url" name="imagen_url" type="text" class="block w-full mt-1"
                  :value="old('imagen_url', $artista->imagen_url ?? '')" />
    <x-input-error :messages="$errors->get('imagen_url')" class="mt-2" />
</div>

<div>
    <x-input-label for="spotify_id" value="Spotify ID (opcional)" />
    <x-text-input id="spotify_id" name="spotify_id" type="text" class="block w-full mt-1"
                  :value="old('spotify_id', $artista->spotify_id ?? '')" />
    <x-input-error :messages="$errors->get('spotify_id')" class="mt-2" />
</div>