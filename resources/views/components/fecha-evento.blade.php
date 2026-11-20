@props(['fecha'])

<div {{ $attributes->class('flex flex-col items-center justify-center w-14 py-1.5 leading-none text-center bg-white rounded-xl shadow-lg shrink-0') }}>
    <span class="text-[11px] font-bold tracking-wider uppercase text-fuchsia-600">{{ str_replace('.', '', $fecha->locale(app()->getLocale())->isoFormat('MMM')) }}</span>
    <span class="mt-0.5 text-2xl font-extrabold text-gray-900">{{ $fecha->format('d') }}</span>
</div>
