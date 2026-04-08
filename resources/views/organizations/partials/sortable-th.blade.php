@php
    $currentSort = request('sort', 'name');
    $currentDirection = request('direction', 'asc');

    $isCurrent = $currentSort === $field;
    $nextDirection = ($isCurrent && $currentDirection === 'asc') ? 'desc' : 'asc';

    $query = array_merge(request()->query(), [
        'sort' => $field,
        'direction' => $nextDirection,
    ]);
@endphp

<a href="{{ route('organizations.index', $query) }}"
   class="text-decoration-none text-dark d-inline-flex align-items-center gap-1 fw-semibold">
    <span>{{ $label }}</span>

    @if($isCurrent)
        <span class="small text-primary">
            {{ $currentDirection === 'asc' ? '↑' : '↓' }}
        </span>
    @endif
</a>