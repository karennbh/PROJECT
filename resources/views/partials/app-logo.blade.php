@props([
    'theme' => 'dark',
    'compact' => false,
])

@php
    $isDark = $theme === 'dark';

    $wrapperClass = $compact
        ? 'inline-flex items-center'
        : 'inline-flex items-center';

    $imageClass = $compact
        ? 'h-10 w-auto'
        : 'h-12 w-auto';

    $filterStyle = $isDark
        ? 'filter: drop-shadow(0 6px 18px rgba(0, 0, 0, 0.22));'
        : 'filter: drop-shadow(0 6px 18px rgba(14, 165, 233, 0.14));';
@endphp

<div {{ $attributes->class([$wrapperClass]) }}>
    <img
        src="{{ asset('assets/logo.png') }}"
        alt="SMART ELECTRIC VEHICLE CENTER OF EXCELLENCE"
        class="{{ $imageClass }}"
        style="{{ $filterStyle }}"
    >
</div>
