@extends('layouts.app')

@section('title', $lead->display_name)

@section('topbar_title', 'Lead')
@section('topbar_subtitle', 'Scheda lead')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column gap-4">

        @if(session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm crm-card--header-actions">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                    <div class="min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <x-icon group="entities" name="lead" />
                            <h2 class="h4 mb-0">{{ $lead->display_name }}</h2>

                            <span class="crm-badge crm-badge--muted">
                                {{ $lead->status?->name ?? 'Senza stato' }}
                            </span>

                            @unless($lead->is_active)
                                <span class="crm-badge crm-badge--muted">Non attivo</span>
                            @endunless
                        </div>

                        <div class="crm-text-muted">{{ $lead->name }}</div>

                        @if($lead->isConverted())
                            <div class="mt-2 crm-text-muted small">
                                Convertito il {{ $lead->converted_at->format('d/m/Y') }}
                                @if($lead->convertedByUser)
                                    da {{ $lead->convertedByUser->name }}
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @include('components.crm.row-actions', [
                            'edit' => route('leads.edit', $lead),
                            'delete' => route('leads.destroy', $lead),
                            'deleteConfirm' => 'Confermi l\'eliminazione di questo lead?',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-semibold mb-3">Contatto</h3>

                        <dl class="row mb-0">
                            <dt class="col-5 col-lg-4 fw-semibold">Nome</dt>
                            <dd class="col-7 col-lg-8">{{ $lead->full_name ?: '—' }}</dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Azienda dichiarata</dt>
                            <dd class="col-7 col-lg-8">{{ $lead->company_name ?: '—' }}</dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Persona in anagrafica</dt>
                            <dd class="col-7 col-lg-8">
                                @if($lead->person)
                                    <a href="{{ route('people.show', $lead->person) }}" class="crm-entity-link">
                                        {{ $lead->person->full_name }}
                                    </a>
                                @else
                                    <span class="crm-text-muted">Non collegata</span>
                                @endif
                            </dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Organizzazione</dt>
                            <dd class="col-7 col-lg-8 mb-0">
                                @if($lead->organization)
                                    <a href="{{ route('organizations.show', $lead->organization) }}" class="crm-entity-link">
                                        {{ $lead->organization->display_name }}
                                    </a>
                                @else
                                    <span class="crm-text-muted">Non collegata</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-semibold mb-3">Gestione</h3>

                        <dl class="row mb-0">
                            <dt class="col-5 col-lg-4 fw-semibold">Fonte</dt>
                            <dd class="col-7 col-lg-8">{{ $lead->source?->name ?? '—' }}</dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Assegnato a</dt>
                            <dd class="col-7 col-lg-8">{{ $lead->assignedUser?->name ?? '—' }}</dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Valore stimato</dt>
                            <dd class="col-7 col-lg-8">
                                {{ $lead->estimated_value !== null ? '€ ' . number_format((float) $lead->estimated_value, 2, ',', '.') : '—' }}
                            </dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Chiusura prevista</dt>
                            <dd class="col-7 col-lg-8">
                                {{ $lead->expected_close_date?->format('d/m/Y') ?? '—' }}
                            </dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Chiuso il</dt>
                            <dd class="col-7 col-lg-8">
                                {{ $lead->closed_at?->format('d/m/Y') ?? '—' }}
                            </dd>

                            <dt class="col-5 col-lg-4 fw-semibold">Motivo perdita</dt>
                            <dd class="col-7 col-lg-8 mb-0">{{ $lead->lost_reason ?: '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            @if($lead->description)
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-semibold mb-3">Descrizione</h3>
                            <p class="mb-0">{!! nl2br(e($lead->description)) !!}</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>
@endsection
