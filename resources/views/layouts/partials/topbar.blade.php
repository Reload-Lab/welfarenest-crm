<div class="crm-topbar d-flex align-items-center justify-content-between px-3 px-lg-4">
    <div class="d-flex align-items-center gap-2">
        <button
            type="button"
            class="btn btn-outline-secondary d-lg-none"
            id="sidebarToggleMobile"
            aria-label="Apri menu"
        >
            ☰
        </button>

        <div>
            <div class="fw-semibold">
                @yield('topbar_title', 'CRM')
            </div>
            <div class="small text-muted d-none d-md-block">
                @yield('topbar_subtitle', 'Gestione operativa')
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="text-end d-none d-sm-block">
            <div class="fw-semibold">{{ auth()->user()->name ?? 'Utente' }}</div>
            <div class="small text-muted">{{ auth()->user()->email ?? '' }}</div>
        </div>

        <div class="dropdown">
            <button
                class="btn btn-light border dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                Account
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="#">Profilo</a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>