@php
    $menuItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => '⌂',
            'patterns' => ['dashboard'],
        ],
        [
            'label' => 'Clienti',
            'route' => 'organizations.index',
            'icon' => '◫',
            'patterns' => ['organizations.*'],
        ],
        [
            'label' => 'Fornitori',
            'route' => null,
            'icon' => '▣',
            'patterns' => [],
        ],
        [
            'label' => 'Contatti',
            'route' => null,
            'icon' => '◌',
            'patterns' => [],
        ],
        [
            'label' => 'Lead',
            'route' => null,
            'icon' => '◎',
            'patterns' => [],
        ],
        [
            'label' => 'Invio Newsletter',
            'route' => null,
            'icon' => '✉',
            'patterns' => [],
        ],
        [
            'label' => 'Esportazioni',
            'route' => null,
            'icon' => '⇩',
            'patterns' => [],
        ],
        [
            'label' => 'Impostazioni',
            'route' => null,
            'icon' => '⚙',
            'patterns' => [],
        ],
    ];

    $isActive = function (array $patterns) {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }
        return false;
    };
@endphp

<aside class="crm-sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="sidebar-brand-link">
            <span class="sidebar-brand-logo">R</span>
            <span class="sidebar-brand-text">Reload CRM</span>
        </a>

        <button type="button" class="sidebar-toggle-btn d-none d-lg-inline-flex" id="sidebarToggleDesktop" aria-label="Comprimi menu">
            ☰
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section-title">CRM</div>

        @foreach($menuItems as $item)
            @php
                $active = $isActive($item['patterns']);
            @endphp

            @if($item['route'])
                <a
                    href="{{ route($item['route']) }}"
                    class="sidebar-link {{ $active ? 'active' : '' }}"
                >
                    <span class="sidebar-link-icon">{{ $item['icon'] }}</span>
                    <span class="sidebar-link-text">{{ $item['label'] }}</span>
                </a>
            @else
                <a
                    href="#"
                    class="sidebar-link"
                    onclick="return false;"
                    style="opacity: .70;"
                >
                    <span class="sidebar-link-icon">{{ $item['icon'] }}</span>
                    <span class="sidebar-link-text">
                        {{ $item['label'] }}
                        <small class="d-block text-white-50">Presto disponibile</small>
                    </span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer-text">
            <div class="fw-semibold text-white">Reload CRM</div>
            <div>Versione iniziale</div>
        </div>
    </div>
</aside>