@php
    use App\Models\ConsentType;
@endphp

@extends('layouts.app')

@section('title', 'Account WN+')

@section('content')

    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $account->full_name }}</h1>
            <div class="text-muted">{{ $account->email }}</div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @include('wn-plus.accounts.partials.invitation-actions', ['account' => $account])

            <x-crm.row-actions
                :edit="route('wn-plus.accounts.edit', $account)"
            />
        </div>
    </div>


    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $account->full_name }}</h1>
            <div class="text-muted">{{ $account->email }}</div>
        </div>

        <a href="{{ route('wn-plus.accounts.index') }}" class="btn btn-outline-secondary">
            Torna agli utenti
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <strong>Dati account</strong>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Organizzazione</dt>
                        <dd class="col-sm-8">
                            {{ $account->organization?->name ?? $account->organization?->legal_name ?? '—' }}
                        </dd>

                        <dt class="col-sm-4">Persona CRM</dt>
                        <dd class="col-sm-8">
                            @if($account->person)
                                {{ $account->person->first_name }} {{ $account->person->last_name }}
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Ruolo</dt>
                        <dd class="col-sm-8">{{ $account->role?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Livello</dt>
                        <dd class="col-sm-8">{{ $account->level?->name ?? '—' }}</dd>

                        <dt class="col-sm-4">Tipo account</dt>
                        <dd class="col-sm-8">{{ $account->account_type === 'manager' ? 'Referente' : 'Utente semplice' }}</dd>

                        <dt class="col-sm-4">Stato</dt>
                        <dd class="col-sm-8">{{ ucfirst($account->status) }}</dd>

                        <dt class="col-sm-4">Max utenti</dt>
                        <dd class="col-sm-8">{{ $account->max_users ?? '—' }}</dd>

                        <dt class="col-sm-4">Slot disponibili</dt>
                        <dd class="col-sm-8">{{ $account->available_slots ?? '—' }}</dd>

                        <dt class="col-sm-4">Ultimo accesso</dt>
                        <dd class="col-sm-8">{{ $account->last_login_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card crm-card">

                        
                <div class="card-header">
                    <div>
                        <h5>Utenti gestiti</h5>
                    </div>

                    @if($account->account_type === 'manager' && $account->available_slots > 0)
                        <x-crm.icon-button
                            icon="add"
                            icon-group="actions"
                            title="Nuovo utente"
                            href="{{ route('wn-plus.accounts.users.create', $account) }}"
                        />
                    @endif
                </div>

                <div class="card-body">
                        @forelse($account->invitedAccounts as $child)
                            @php
                                $lastInvitation = $child->invitations->sortByDesc('created_at')->first();
                            @endphp

                            <div class="border rounded p-3 mb-2 d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $child->full_name }}</div>
                                    <div class="text-muted small">{{ $child->email }}</div>
                                    <div class="small mt-1">
                                        Stato: {{ ucfirst($child->status) }}
                                        @if($lastInvitation?->accepted_at)
                                            · Invito accettato il {{ $lastInvitation->accepted_at->format('d/m/Y') }}
                                        @elseif($lastInvitation)
                                            · Invito in attesa
                                        @endif
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="crm-status-badge crm-status-badge--{{ $child->consentBadgeVariant(ConsentType::PRIVACY_NOTICE) }} border-0"
                                    title="{{ $child->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                    aria-label="{{ $child->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#consentsModal-{{ $child->id }}">
                                    <x-icon group="entities" name="consent" />
                                </button>
                            </div>

                            @include('people.partials.show.consents-modal', [
                                'owner' => $child,
                                'modalId' => 'consentsModal-' . $child->id,
                            ])
                        @empty
                            <div class="text-muted">
                                Nessun utente semplice creato da questo referente.
                            </div>
                        @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @include('wn-plus.accounts.partials.invitation-status', ['account' => $account])

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <strong>Azioni</strong>
                </div>

                <div class="card-body d-grid gap-2">
                    @include('wn-plus.accounts.partials.invitation-actions', ['account' => $account])

                    <a href="{{ route('wn-plus.accounts.edit', $account) }}" class="btn btn-outline-secondary">
                        Modifica account
                    </a>

                    <button type="button" class="btn btn-outline-danger" disabled>
                        Sospendi account
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection