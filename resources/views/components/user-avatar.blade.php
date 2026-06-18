@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-16 w-16 text-lg',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($user->avatarUrl())
    <img
        {{ $attributes->merge(['class' => "rounded-full object-cover shrink-0 {$sizeClass}"]) }}
        src="{{ $user->avatarUrl() }}"
        alt="{{ $user->name }}"
    >
@else
    <span
        {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-full bg-primary/10 text-primary font-medium shrink-0 {$sizeClass}"]) }}
        aria-hidden="true"
    >
        {{ $user->initials() }}
    </span>
@endif
