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
    </div>

    <form method="POST" action="{{ route('wn-plus.logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">Esci</button>
    </form>
</div>