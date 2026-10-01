@php
    // La voce "I miei utenti" compare solo ai referenti. Il controllo vero sta nel
    // controller: qui si evita solo di mostrare una porta che porterebbe a un 403.
    $isManager = $account->account_type === 'manager';
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex gap-3">
        <a href="{{ route('wn-plus.portal.dashboard') }}"
           class="fw-semibold {{ $active === 'dashboard' ? 'text-dark' : 'text-muted' }}">
            Dashboard
        </a>
        <a href="{{ route('wn-plus.portal.profile') }}"
           class="fw-semibold {{ $active === 'profile' ? 'text-dark' : 'text-muted' }}">
            Profilo
        </a>

        @if ($isManager)
            <a href="{{ route('wn-plus.portal.users.index') }}"
               class="fw-semibold {{ $active === 'users' ? 'text-dark' : 'text-muted' }}">
                I miei utenti
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('wn-plus.logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">Esci</button>
    </form>
</div>
