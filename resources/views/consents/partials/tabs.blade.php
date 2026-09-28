<ul class="nav nav-tabs mb-4">
    @foreach([
        ['route' => 'consents.index', 'label' => 'Consensi registrati'],
        ['route' => 'consents.requests', 'label' => 'Richieste inviate'],
    ] as $tab)
        <li class="nav-item">
            <a
                href="{{ route($tab['route']) }}"
                class="nav-link {{ request()->routeIs($tab['route']) ? 'active' : '' }}"
            >
                {{ $tab['label'] }}
            </a>
        </li>
    @endforeach
</ul>
