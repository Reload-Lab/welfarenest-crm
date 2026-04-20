<header class="crm-topbar">
    <div class="crm-topbar-left">
        <button type="button" class="crm-topbar-menu-btn d-lg-none" id="sidebarToggleMobile" aria-label="Apri menu">
            <x-icon group="navigation" name="menu" />
        </button>

        <div class="crm-topbar-heading">
            <h1 class="crm-topbar-title">@yield('topbar_title', 'Dashboard')</h1>
            @hasSection('topbar_subtitle')
                <p class="crm-topbar-subtitle">@yield('topbar_subtitle')</p>
            @endif
        </div>
    </div>

    <div class="crm-topbar-right">
        <div class="dropdown">
            <button
                class="crm-topbar-user dropdown-toggle"
                type="button"
                id="topbarUserMenu"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <span class="crm-topbar-user-avatar">
                    <x-icon group="entities" name="person" />
                </span>

                <span class="crm-topbar-user-text">
                    {{ auth()->user()->name ?? 'Amministratore' }}
                </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end crm-topbar-dropdown shadow-sm border-0" aria-labelledby="topbarUserMenu">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                        <x-icon group="entities" name="person" class="icon-sm" />
                        <span>Profilo</span>
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                            <x-icon group="actions" name="logout" class="icon-sm" />
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>


