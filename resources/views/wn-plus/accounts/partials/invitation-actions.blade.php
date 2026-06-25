@php
    $hasPendingInvitation = $account->invitations
        ->whereNull('accepted_at')
        ->where('expires_at', '>', now())
        ->isNotEmpty();

    $hasAnyInvitation = $account->invitations->isNotEmpty();
@endphp

@if($account->status !== 'active')
    <form method="POST" action="{{ route('wn-plus.accounts.invite', $account) }}">
        @csrf

        <button type="submit" class="btn btn-primary w-100">
            @if($hasPendingInvitation || $hasAnyInvitation)
                Reinvia invito
            @else
                Invia invito
            @endif
        </button>
    </form>
@else
    <button type="button" class="btn btn-outline-success w-100" disabled>
        Account attivo
    </button>
@endif