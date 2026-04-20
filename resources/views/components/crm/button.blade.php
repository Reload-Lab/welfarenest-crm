@props([
    'tag' => 'button',
    'type' => 'button',
    'variant' => 'primary',   // primary | outline-secondary | light
    'icon' => null,
    'iconGroup' => 'actions',
    'href' => null,
    'fullWidth' => false,
])

@php
    $baseClass = 'btn btn-inline';
    $variantClass = 'btn-' . $variant;
    $widthClass = $fullWidth ? ' w-100 justify-content-center' : '';

    $classes = trim($baseClass . ' ' . $variantClass . $widthClass);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :group="$iconGroup" :name="$icon" />
        @endif

        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-icon :group="$iconGroup" :name="$icon" />
        @endif

        <span>{{ $slot }}</span>
    </button>
@endif