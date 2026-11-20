@props(['artista'])

@if ($artista->imagen_url)
    <img src="{{ $artista->imagen_url }}" alt="Foto de {{ $artista->nombre }}" {{ $attributes->class('object-cover rounded-full') }}>
@else
    <span aria-hidden="true" {{ $attributes->class('inline-flex items-center justify-center font-bold text-white uppercase rounded-full bg-gradient-to-br from-fuchsia-500 to-purple-700') }}>{{ mb_substr($artista->nombre, 0, 1) }}</span>
@endif
