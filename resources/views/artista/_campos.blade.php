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