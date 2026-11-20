@props(['genero'])

@php
    $colores = [
        'Rock' => 'bg-rose-100 text-rose-700',
        'Pop' => 'bg-pink-100 text-pink-700',
        'Reggaetón' => 'bg-amber-100 text-amber-800',
        'Salsa' => 'bg-orange-100 text-orange-700',
        'Vallenato' => 'bg-emerald-100 text-emerald-700',
        'Electrónica' => 'bg-cyan-100 text-cyan-800',
        'Hip Hop' => 'bg-zinc-200 text-zinc-800',
        'Jazz' => 'bg-indigo-100 text-indigo-700',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full', $colores[$genero] ?? 'bg-purple-100 text-purple-700']) }}>{{ $genero }}</span>
