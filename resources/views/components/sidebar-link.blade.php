@props(['active' => false, 'icono'])

<a {{ $attributes->class([
    'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition',
    'bg-gradient-to-r from-fuchsia-600 to-purple-600 text-white shadow-lg shadow-purple-900/40' => $active,
    'text-gray-300 hover:bg-gray-800 hover:text-white' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    <x-icono :nombre="$icono" />
    <span>{{ $slot }}</span>
</a>
