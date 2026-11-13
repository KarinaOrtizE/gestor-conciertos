<div>
    <x-input-label for="titulo" value="Título del evento" />
    <x-text-input id="titulo" name="titulo" type="text" class="block w-full mt-1"
                  :value="old('titulo', $evento->titulo ?? '')" required />
    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
</div>

<div>
    <x-input-label for="artista_id" value="Artista" />
    <select id="artista_id" name="artista_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
        <option value="">Selecciona un artista</option>
        @foreach ($artistas as $artista)
            <option value="{{ $artista->id }}"
                @selected(old('artista_id', $evento->artista_id ?? '') == $artista->id)>
                {{ $artista->nombre }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('artista_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="fecha" value="Fecha y hora" />
    <x-text-input id="fecha" name="fecha" type="datetime-local" class="block w-full mt-1"
                  :value="old('fecha', isset($evento) ? $evento->fecha->format('Y-m-d\TH:i') : '')" required />
    <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="lugar" value="Lugar" />
        <x-text-input id="lugar" name="lugar" type="text" class="block w-full mt-1"
                      :value="old('lugar', $evento->lugar ?? '')" required />
        <x-input-error :messages="$errors->get('lugar')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="ciudad" value="Ciudad" />
        <x-text-input id="ciudad" name="ciudad" type="text" class="block w-full mt-1"
                      :value="old('ciudad', $evento->ciudad ?? '')" required />
        <x-input-error :messages="$errors->get('ciudad')" class="mt-2" />
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <x-input-label for="precio" value="Precio (COP)" />
        <x-text-input id="precio" name="precio" type="number" step="0.01" min="0" class="block w-full mt-1"
                      :value="old('precio', $evento->precio ?? '')" required />
        <x-input-error :messages="$errors->get('precio')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="genero" value="Género musical" />
        <select id="genero" name="genero" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" required>
            <option value="">Selecciona un género</option>
            @foreach ($generos as $g)
                <option value="{{ $g }}" @selected(old('genero', $evento->genero ?? '') == $g)>{{ $g }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('genero')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="afiche" value="Afiche del evento (JPG, PNG o WEBP, máx. 2 MB)" />
    @if (isset($evento) && $evento->afiche)
        <img src="{{ asset('storage/'.$evento->afiche) }}" class="object-cover w-40 mb-2 rounded" alt="Afiche actual">
    @endif
    <input id="afiche" name="afiche" type="file" accept="image/*" class="block w-full mt-1">
    <x-input-error :messages="$errors->get('afiche')" class="mt-2" />
</div>