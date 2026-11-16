@props(['evento', 'marcado' => false, 'compacto' => false])

<form method="POST" action="{{ route('eventos.favorito', $evento) }}" {{ $attributes->class('inline-block') }}
      x-data="botonFavorito({{ $marcado ? 'true' : 'false' }})" @submit.prevent="alternar($el)">
    @csrf
    <button type="submit" :disabled="enviando"
            aria-pressed="{{ $marcado ? 'true' : 'false' }}" :aria-pressed="marcado.toString()"
            @if ($compacto) aria-label="Favorito: {{ $evento->titulo }}" title="Favorito" @endif
            @class([
                'text-rose-600 disabled:opacity-50',
                'p-1 rounded-full transition hover:scale-110' => $compacto,
                'inline-flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50' => ! $compacto,
            ])>
        <x-icono nombre="corazon" fill="{{ $marcado ? 'currentColor' : 'none' }}" x-bind:fill="marcado ? 'currentColor' : 'none'" />
        @unless ($compacto)
            <span class="text-gray-700" x-text="marcado ? 'En favoritos' : 'Agregar a favoritos'">{{ $marcado ? 'En favoritos' : 'Agregar a favoritos' }}</span>
        @endunless
    </button>
</form>
