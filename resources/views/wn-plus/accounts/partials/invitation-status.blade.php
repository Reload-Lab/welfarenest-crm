@php
    $latestInvitation = $account->invitations
        ->sortByDesc('created_at')
        ->first();

    $pendingInvitation = $account->invitations
        ->whereNull('accepted_at')
        ->where('expires_at', '>', now())
        ->sortByDesc('created_at')
        ->first();
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0">
        <strong>Stato invito</strong>
    </div>

    <div class="card-body">
        @if($account->status === 'active')
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                Account attivo
            </span>
            <div class="text-muted small mt-2">
                Attivato il {{ $account->email_verified_at?->format('d/m/Y H:i') ?? '—' }}.
            </div>
        @elseif($pendingInvitation)
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                Invito inviato
            </span>
            <div class="text-muted small mt-2">
                Scade il {{ $pendingInvitation->expires_at?->format('d/m/Y H:i') }}.
            </div>
        @elseif($latestInvitation && $latestInvitation->accepted_at)
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                Invito accettato
            </span>
            <div class="text-muted small mt-2">
                Accettato il {{ $latestInvitation->accepted_at?->format('d/m/Y H:i') }}.
            </div>
        @elseif($latestInvitation && $latestInvitation->expires_at?->isPast())
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                Invito scaduto
            </span>
            <div class="text-muted small mt-2">
                Ultima scadenza: {{ $latestInvitation->expires_at?->format('d/m/Y H:i') }}.
            </div>
        @else
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                Invito non inviato
            </span>
            <div class="text-muted small mt-2">
                L’utente non ha ancora ricevuto il link di attivazione.
            </div>
        @endif
    </div>
</div>