@php
    // La voce "I miei utenti" compare solo ai referenti. Il controllo vero sta nel
    // controller: qui si evita solo di mostrare una porta che porterebbe a un 403.
    $isManager = $account->account_type === 'manager';

    // Indirizzo della pagina del sito WN+ da cui si entra nell'area riservata.
    // Finché non è configurato il pulsante non compare, invece di mandare la
    // persona sulla home dove resterebbe anonima.
    $siteUrl = trim((string) config('services.wn_plus_site.area_url'));
@endphp

<nav class="wn-portal-bar mb-4">
    <div class="wn-portal-bar__links">
        <a href="{{ route('wn-plus.portal.dashboard') }}"
           class="wn-portal-bar__link {{ $active === 'dashboard' ? 'is-active' : '' }}">
            Area riservata
        </a>

        <a href="{{ route('wn-plus.portal.profile') }}"
           class="wn-portal-bar__link {{ $active === 'profile' ? 'is-active' : '' }}">
            Il mio profilo
        </a>

        @if ($isManager)
            <a href="{{ route('wn-plus.portal.users.index') }}"
               class="wn-portal-bar__link {{ $active === 'users' ? 'is-active' : '' }}">
                I miei utenti
            </a>
        @endif
    </div>

    <div class="wn-portal-bar__actions">
        @if ($siteUrl !== '')
            <a href="{{ $siteUrl }}" class="wn-portal-site-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12H19M19 12L13 6M19 12L13 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Vai a Welfare Nest Plus
            </a>
        @endif

        <form method="POST" action="{{ route('wn-plus.logout') }}">
            @csrf
            <button type="submit" class="wn-portal-logout">Esci</button>
        </form>
    </div>
</nav>
