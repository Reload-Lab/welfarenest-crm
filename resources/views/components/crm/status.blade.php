@props([
    'label' => null,
    'variant' => 'muted',
    'iconGroup' => null,
    'iconName' => null,
    'mode' => 'full',
])

@if(filled($label))
    @if($mode === 'icon')
        <span
            {{ $attributes->class([
                'crm-status-icon',
                'crm-status-icon--' . $variant,
            ]) }}
            title="{{ $label }}"
            aria-label="{{ $label }}"
        >
            @if($iconGroup && $iconName)
                <x-icon :group="$iconGroup" :name="$iconName" />
            @endif
        </span>
    @else
        <span
            {{ $attributes->class([
                'crm-status-badge',
                'crm-status-badge--' . $variant,
            ]) }}
        >
            @if($iconGroup && $iconName)
                <x-icon :group="$iconGroup" :name="$iconName" />
            @endif

            <span>{{ $label }}</span>
        </span>
    @endif
@endif