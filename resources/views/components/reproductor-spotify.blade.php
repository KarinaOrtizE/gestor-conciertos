@props(['spotifyId', 'compacto' => false])

{{-- Reproductor oficial de Spotify: no necesita credenciales. Sin sesión de Spotify en el navegador reproduce avances de 30 segundos. --}}
<iframe src="https://open.spotify.com/embed/artist/{{ $spotifyId }}"
        {{ $attributes->merge(['title' => 'Reproductor de Spotify', 'class' => 'w-full border-0 rounded-xl']) }}
        height="{{ $compacto ? 152 : 352 }}" loading="lazy"
        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
