@php
    $defaultSort = $defaultSort ?? 'name';

    $currentSort = request('sort', $defaultSort);
    $currentDirection = request('direction', 'asc');

    $isCurrent = $currentSort === $field;
    $nextDirection = ($isCurrent && $currentDirection === 'asc') ? 'desc' : 'asc';

    $query = array_merge(request()->query(), [
        'sort' => $field,
        'direction' => $nextDirection,
    ]);
@endphp

<a href="{{ url()->current() . '?' . http_build_query($query) }}"
   class="crm-sort-link {{ $isCurrent ? 'is-active' : '' }}">
    <span>{{ $label }}</span>

    <span class="crm-sort-icon" aria-hidden="true">
        @if($isCurrent)
            @if($currentDirection === 'asc')
                <x-icon group="actions" name="chevron-up" />
            @else
                <x-icon group="actions" name="chevron-down" />
            @endif
        @else
            <x-icon group="actions" name="sort" />
        @endif
    </span>
</a>