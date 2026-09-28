@extends('layouts.app')

@php
    $indexRoute = 'consents.index';
@endphp

@section('topbar_title', 'Consensi')
@section('topbar_subtitle', 'Registro dei consensi registrati')

@section('content')
<div class="container-fluid">

    @include('consents.partials.tabs')

    <p class="crm-text-muted mb-4">
        Ogni consenso concesso, negato o revocato, con la versione dell’informativa
        a cui si riferisce e l’origine da cui è stato raccolto.
    </p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRoute) }}">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-lg">
                        <label for="search" class="form-label fw-semibold">Ricerca</label>
                        <div class="position-relative">
                            <span class="crm-filter-search-icon">
                                <x-icon group="actions" name="search" />
                            </span>

                            <input
                                type="text"
                                name="search"
                                id="search"
                                value="{{ $filters['search'] }}"
                                class="form-control crm-filter-search-input"
                                placeholder="Nome o cognome"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="consent_type_id" class="form-label fw-semibold">Tipo di consenso</label>
                        <select name="consent_type_id" id="consent_type_id" class="form-select">
                            <option value="">Tutti</option>
                            @foreach($consentTypes as $consentType)
                                <option
                                    value="{{ $consentType->id }}"
                                    {{ (string) $filters['consent_type_id'] === (string) $consentType->id ? 'selected' : '' }}
                                >
                                    {{ $consentType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="status" class="form-label fw-semibold">Esito</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tutti</option>
                            @foreach(['granted' => 'Concesso', 'denied' => 'Negato', 'revoked' => 'Revocato'] as $value => $label)
                                <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="origin" class="form-label fw-semibold">Origine</label>
                        <select name="origin" id="origin" class="form-select">
                            <option value="">Tutte</option>
                            <option value="automatic" {{ $filters['origin'] === 'automatic' ? 'selected' : '' }}>
                                Raccolto dall’interessato
                            </option>
                            <option value="manual" {{ $filters['origin'] === 'manual' ? 'selected' : '' }}>
                                Inserito a mano
                            </option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="from" class="form-label fw-semibold">Dal</label>
                        <input type="date" name="from" id="from" value="{{ $filters['from'] }}" class="form-control">
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="to" class="form-label fw-semibold">Al</label>
                        <input type="date" name="to" id="to" value="{{ $filters['to'] }}" class="form-control">
                    </div>

                    <div class="col-12 col-lg">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <x-crm.button type="submit" icon="search" variant="primary">Cerca</x-crm.button>
                            <x-crm.button href="{{ route($indexRoute) }}" icon="reset" variant="outline-secondary">
                                Reset
                            </x-crm.button>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}">
            </form>
        </div>
    </div>

    <div class="crm-table-card">
        <div class="crm-table-responsive">
            <table class="table crm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="crm-cell-start">
                            @include('components.crm.sortable-th', [
                                'label' => 'Persona',
                                'field' => 'person',
                                'defaultSort' => 'event_at',
                            ])
                        </th>
                        <th>Consenso</th>
                        <th>
                            @include('components.crm.sortable-th', [
                                'label' => 'Esito',
                                'field' => 'status',
                                'defaultSort' => 'event_at',
                            ])
                        </th>
                        <th>
                            @include('components.crm.sortable-th', [
                                'label' => 'Data',
                                'field' => 'event_at',
                                'defaultSort' => 'event_at',
                            ])
                        </th>
                        <th>Origine</th>
                        <th class="crm-cell-end">Registrato da</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $row)
                        <tr class="crm-table__row">
                            <td class="crm-cell-start">
                                <a href="{{ route('people.show', $row->owner_id) }}" class="crm-entity-link">
                                    {{ trim($row->person_last_name.' '.$row->person_first_name) ?: '#'.$row->owner_id }}
                                </a>
                            </td>

                            <td>
                                <div>{{ $row->consentType?->name ?? '—' }}</div>
                                @if($row->consentVersion)
                                    <div class="small crm-text-muted">
                                        {{ $row->consentVersion->title ?? $row->consentVersion->version_code }}
                                    </div>
                                @else
                                    <div class="small crm-text-muted">Nessuna informativa collegata</div>
                                @endif
                            </td>

                            <td>
                                <span class="crm-badge crm-badge--{{ $row->status_variant }}">
                                    {{ $row->status_label }}
                                </span>
                            </td>

                            <td>
                                <span class="text-nowrap">{{ $row->effective_at?->format('d/m/Y H:i') ?? '—' }}</span>
                            </td>

                            <td>
                                <span class="crm-badge crm-badge--{{ $row->is_manual ? 'warning' : 'muted' }}">
                                    {{ $row->source_label }}
                                </span>

                                @if(filled($row->notes))
                                    <div class="small crm-text-muted text-truncate" style="max-width: 22rem;">
                                        {{ $row->notes }}
                                    </div>
                                @endif
                            </td>

                            <td class="crm-cell-end">
                                {{ $row->createdByUser?->name ?? 'Nessun utente' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessun consenso trovato</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono consensi da mostrare con i filtri correnti.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('consents.partials.footer')
    </div>

</div>
@endsection
