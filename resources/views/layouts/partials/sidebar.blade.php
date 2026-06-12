@php
    $menuItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon_group' => 'navigation',
            'icon_name' => 'dashboard',
            'patterns' => ['dashboard'],
        ],
        [
            'label' => 'Clienti',
            'route' => 'clients.index',
            'icon_group' => 'entities',
            'icon_name' => 'client',
            'patterns' => ['clients.*'],
        ],
        [
            'label' => 'Fornitori',
            'route' => 'suppliers.index',
            'icon_group' => 'entities',
            'icon_name' => 'supplier',
            'patterns' => ['suppliers.*'],
        ],
        [
            'label' => 'Persone',
            'route' => 'people.index',
            'icon_group' => 'entities',
            'icon_name' => 'person',
            'patterns' => ['people.*'],
        ],
        [
            'label' => 'Lead',
            'route' => null,
            'icon_group' => 'entities',
            'icon_name' => 'lead',
            'patterns' => [],
        ],
        [
            'label' => 'WN+',
            'route' => 'wn-plus.accounts.index',
            'icon_group' => 'entities',
            'icon_name' => 'welfarenestplus',
            'patterns' => ['wn-plus.accounts.*'],
        ],
        [
            'label' => 'Invio Newsletter',
            'route' => null,
            'icon_group' => 'contact',
            'icon_name' => 'email',
            'patterns' => [],
        ],
        [
            'label' => 'Esportazioni',
            'route' => null,
            'icon_group' => 'navigation',
            'icon_name' => 'reports',
            'patterns' => [],
        ],
        [
            'label' => 'Impostazioni',
            'route' => null,
            'icon_group' => 'navigation',
            'icon_name' => 'settings',
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
            <span class="sidebar-brand-logo">
                <img
                    src="{{ asset('images/logo-wn-pittogramma.png') }}"
                    alt="Welfare Nest"
                    class="sidebar-logo-icon"
                >
            </span>

            <span class="sidebar-brand-text">
                <img
                    src="{{ asset('images/logo-wn.png') }}"
                    alt="Welfare Nest"
                    class="sidebar-logo-full"
                >
            </span>
        </a>
    </div>


    <nav class="sidebar-nav">

        @foreach($menuItems as $item)
            @php
                $active = $isActive($item['patterns']);
            @endphp

            @if($item['route'])
                <a
                    href="{{ route($item['route']) }}"
                    class="sidebar-link {{ $active ? 'active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <x-icon :group="$item['icon_group']" :name="$item['icon_name']" />
                    </span>
                    <span class="sidebar-link-text">{{ $item['label'] }}</span>
                </a>
            @else
                <a
                    href="#"
                    class="sidebar-link"
                    onclick="return false;"
                    style="opacity: .70;"
                >
                    <span class="sidebar-link-icon">
                        <x-icon :group="$item['icon_group']" :name="$item['icon_name']" />
                    </span>
                    <span class="sidebar-link-text">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer-text">
            <div class="fw-semibold text-white">Welfare Nest CRM</div>
            CRM v{{ config('app.version') }}
        </div>
        

        <div class="sidebar-footer-toggle">
            <button
                type="button"
                class="sidebar-toggle-btn"
                id="sidebarToggleDesktop"
                aria-label="Comprimi o espandi menu"
            >
                <x-icon group="actions" name="chevron-left" class="sidebar-toggle-icon" />
            </button>
        </div>
    </div>
</aside>