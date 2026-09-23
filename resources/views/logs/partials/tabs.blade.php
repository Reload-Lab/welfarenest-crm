@php
    $tabs = [
        ['route' => 'logs.audit', 'label' => 'Modifiche', 'patterns' => ['logs.audit', 'logs.audit.show']],
        ['route' => 'logs.activity', 'label' => 'Attività', 'patterns' => ['logs.activity']],
        ['route' => 'logs.access', 'label' => 'Accessi', 'patterns' => ['logs.access']],
    ];
@endphp

<ul class="nav nav-tabs mb-4">
    @foreach($tabs as $tab)
        <li class="nav-item">
            <a
                href="{{ route($tab['route']) }}"
                class="nav-link {{ request()->routeIs($tab['patterns']) ? 'active' : '' }}"
            >
                {{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
