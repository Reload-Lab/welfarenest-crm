@props([
    'label' => null,
    'iconGroup' => null,
    'iconName' => null,
    'variant' => 'default',
    'size' => 'sm',
])

@if(filled($label))
    <span {{ $attributes->class([
        'crm-tag',
        'crm-tag--' . $variant,
        'crm-tag--' . $size,
    ]) }}>
        @if($iconGroup && $iconName)
            <x-icon :group="$iconGroup" :name="$iconName" />
        @endif

        <span>{{ $label }}</span>
    </span>
@endif