@props([
    'type' => 'button',
    'href' => null,
    'icon',
    'iconGroup' => 'actions',
    'title' => null,
    'variant' => 'light',
])

@php
    $classes = 'crm-icon-button crm-icon-button-' . $variant;
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => $classes]) }}
       @if($title) title="{{ $title }}" aria-label="{{ $title }}" @endif>
        <x-icon :group="$iconGroup" :name="$icon" />
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => $classes]) }}
            @if($title) title="{{ $title }}" aria-label="{{ $title }}" @endif>
        <x-icon :group="$iconGroup" :name="$icon" />
    </button>
@endif