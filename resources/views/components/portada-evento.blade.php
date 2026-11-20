@props(['evento'])

@php
    // Si el evento no tiene afiche se muestra un degradado según su género.
    $degradados = [
        'Rock' => 'from-rose-500 via-red-600 to-orange-500',
        'Pop' => 'from-pink-400 via-fuchsia-500 to-purple-600',
        'Reggaetón' => 'from-amber-400 via-orange-500 to-pink-600',
        'Salsa' => 'from-orange-400 via-red-500 to-rose-700',
        'Vallenato' => 'from-yellow-400 via-amber-500 to-emerald-600',
        'Electrónica' => 'from-cyan-400 via-blue-600 to-indigo-800',
        'Hip Hop' => 'from-slate-500 via-zinc-700 to-neutral-900',
        'Jazz' => 'from-indigo-500 via-violet-700 to-purple-900',
        'Otro' => 'from-purple-500 via-fuchsia-600 to-pink-500',
    ];
@endphp

@if ($url = $evento->aficheUrl())
    <img src="{{ $url }}" alt="Afiche de {{ $evento->titulo }}" {{ $attributes->class('object-cover') }}>
@else
    <div role="img" aria-label="{{ $evento->titulo }}"
         {{ $attributes->class(['relative flex items-center justify-center overflow-hidden text-white bg-gradient-to-br', $degradados[$evento->genero] ?? $degradados['Otro']]) }}>
        <div class="absolute rounded-full -top-6 -right-6 w-28 h-28 bg-white/15 blur-xl"></div>
        <x-icono nombre="nota" class="w-1/3 h-1/3 max-w-20 max-h-20 opacity-40 drop-shadow" />
    </div>
@endif
