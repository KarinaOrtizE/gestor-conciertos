@props(['user'])

@if ($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="Foto de {{ $user->name }}"
         {{ $attributes->merge(['class' => 'object-cover w-10 h-10 rounded-full shrink-0']) }}>
@else
    <span {{ $attributes->merge(['class' => 'flex items-center justify-center w-10 h-10 font-semibold text-white uppercase bg-purple-700 rounded-full shrink-0']) }}
          aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
@endif
