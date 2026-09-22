@php
    $owner = $owner ?? (isset($person) ? $person : null);

    // Il blocco azione è specifico della Persona: l'invio della richiesta di
    // consenso passa sempre dall'email di riferimento della persona (scelta DPO).
    $consentPerson = $owner instanceof \App\Models\Person ? $owner : null;

    $consentState = $consentPerson?->consentRequestState(\App\Models\ConsentType::PRIVACY_NOTICE);
    $pendingRequest = $consentPerson?->pendingConsentRequest();
    $lastRequest = $consentPerson?->latestConsentRequest();
    $privacyConsent = $consentPerson?->latestConsentByCode(\App\Models\ConsentType::PRIVACY_NOTICE);
    $targetEmail = $consentPerson?->primaryOrFirstEmailContactPoint();

    // Giorni di attesa senza risposta: oltre questa soglia la richiesta aperta
    // viene evidenziata come da sollecitare.
    $daysWaiting = $pendingRequest?->sent_at
        ? (int) $pendingRequest->sent_at->copy()->startOfDay()->diffInDays(now()->startOfDay())
        : null;
    $isStale = $daysWaiting !== null && $daysWaiting >= 3;
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 mb-0">Privacy e consensi</h3>
        </div>

        @error('consent_request')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        @if($consentPerson)
            @php
                $stateBox = match ($consentState) {
                    \App\Models\Person::CONSENT_STATE_GRANTED => [
                        'class' => 'alert-success',
                        'title' => 'Consenso acquisito',
                        'action' => 'Invia di nuovo l\'informativa',
                        'button' => 'btn-outline-secondary',
                        'confirm' => 'Inviare di nuovo l\'informativa a :email?',
                    ],
                    \App\Models\Person::CONSENT_STATE_AWAITING => [
                        'class' => $isStale ? 'alert-warning' : 'alert-info',
                        'title' => 'Richiesta inviata, in attesa di risposta',
                        'action' => 'Sollecita',
                        'button' => $isStale ? 'btn-warning' : 'btn-outline-primary',
                        'confirm' => 'Inviare di nuovo la richiesta di consenso a :email?',
                    ],
                    \App\Models\Person::CONSENT_STATE_REFUSED => [
                        'class' => 'alert-danger',
                        'title' => 'Consenso negato o revocato',
                        'action' => 'Invia nuova richiesta',
                        'button' => 'btn-outline-danger',
                        'confirm' => 'Inviare una nuova richiesta di consenso a :email?',
                    ],
                    \App\Models\Person::CONSENT_STATE_EXPIRED => [
                        'class' => 'alert-warning',
                        'title' => 'Richiesta scaduta senza risposta',
                        'action' => 'Invia nuova richiesta',
                        'button' => 'btn-warning',
                        'confirm' => 'Inviare una nuova richiesta di consenso a :email?',
                    ],
                    default => [
                        'class' => 'alert-secondary',
                        'title' => 'Consenso mancante',
                        'action' => 'Invia richiesta di consenso',
                        'button' => 'btn-primary',
                        'confirm' => 'Inviare la richiesta di consenso a :email?',
                    ],
                };
            @endphp

            <div class="alert {{ $stateBox['class'] }} d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

                <div>
                    <div class="fw-semibold">{{ $stateBox['title'] }}</div>

                    <div class="small mt-1">
                        @switch($consentState)
                            @case(\App\Models\Person::CONSENT_STATE_GRANTED)
                                Acquisito il
                                {{ $privacyConsent?->granted_at?->format('d/m/Y') ?? '—' }}
                                @if($privacyConsent?->consentVersion?->version_code)
                                    · versione {{ $privacyConsent->consentVersion->version_code }}
                                @endif
                                @break

                            @case(\App\Models\Person::CONSENT_STATE_AWAITING)
                                @if($pendingRequest?->sent_at)
                                    Inviata a {{ $pendingRequest->contactPoint?->value ?? '—' }}
                                    il {{ $pendingRequest->sent_at->format('d/m/Y') }}
                                    @if($daysWaiting >= 1)
                                        · nessuna risposta da {{ $daysWaiting }}
                                        {{ $daysWaiting === 1 ? 'giorno' : 'giorni' }}
                                    @endif
                                    · il link scade il {{ $pendingRequest->expires_at?->format('d/m/Y') ?? '—' }}
                                @else
                                    Richiesta creata il
                                    {{ $pendingRequest?->created_at?->format('d/m/Y') ?? '—' }}
                                    ma mai spedita: l'invio dell'email non è andato a buon fine.
                                @endif
                                @break

                            @case(\App\Models\Person::CONSENT_STATE_REFUSED)
                                {{ $privacyConsent?->status === 'revoked' ? 'Revocato' : 'Negato' }} il
                                {{ ($privacyConsent?->revoked_at ?? $privacyConsent?->denied_at)?->format('d/m/Y') ?? '—' }}
                                @break

                            @case(\App\Models\Person::CONSENT_STATE_EXPIRED)
                                Ultima richiesta
                                @if($lastRequest?->sent_at)
                                    inviata il {{ $lastRequest->sent_at->format('d/m/Y') }},
                                @endif
                                scaduta il {{ $lastRequest?->expires_at?->format('d/m/Y') ?? '—' }}
                                senza risposta. Il reinvio genera una nuova richiesta.
                                @break

                            @default
                                Nessuna richiesta di consenso è mai stata inviata a questa persona.
                        @endswitch
                    </div>

                    @if($targetEmail)
                        <div class="small text-muted mt-1">
                            Destinatario: {{ $targetEmail->value }}
                        </div>
                    @endif
                </div>

                <div class="flex-shrink-0">
                    @php
                        $confirmMessage = str_replace(':email', $targetEmail?->value ?? '', $stateBox['confirm']);
                    @endphp

                    <form
                        action="{{ route('people.consent-requests.store', $consentPerson) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm(@js($confirmMessage));"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-sm {{ $stateBox['button'] }}"
                            @disabled(! $targetEmail)
                            title="{{ $targetEmail
                                ? $stateBox['action'] . ' a ' . $targetEmail->value
                                : 'Aggiungi prima un\'email alla persona per poter inviare la richiesta di consenso' }}"
                        >
                            {{ $stateBox['action'] }}
                        </button>
                    </form>
                </div>

            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Consenso</th>
                        <th>Versione</th>
                        <th>Stato</th>
                        <th>Data</th>
                        <th>Origine</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($owner->consents as $consent)
                        <tr>
                            <td>{{ $consent->consentType?->name }}</td>
                            <td>{{ $consent->consentVersion?->version_code ?? '—' }}</td>
                            <td>{{ $consent->status }}</td>
                            <td>
                                {{ $consent->granted_at?->format('d/m/Y')
                                    ?? $consent->requested_at?->format('d/m/Y')
                                    ?? '—' }}
                            </td>
                            <td>{{ $consent->source ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">
                                Nessun consenso registrato.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
