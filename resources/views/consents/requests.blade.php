@extends('layouts.app')

@php
    $indexRoute = 'consents.requests';
@endphp

@section('topbar_title', 'Consensi')
@section('topbar_subtitle', 'Richieste di consenso inviate')

@section('content')
<div class="container-fluid">

    @include('consents.partials.tabs')

    <p class="crm-text-muted mb-4">
        Le richieste inviate via email e il loro esito. Una richiesta in attesa può
        essere rispedita: se il link precedente è ancora valido viene reinviato
        quello, altrimenti ne viene generato uno nuovo.
    </p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

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

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="status" class="form-label fw-semibold">Stato</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tutti</option>
                            @foreach(['pending' => 'In attesa', 'completed' => 'Completata', 'expired' => 'Scaduta'] as $value => $label)
                                <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="from" class="form-label fw-semibold">Inviata dal</label>
                        <input type="date" name="from" id="from" value="{{ $filters['from'] }}" class="form-control">
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="to" class="form-label fw-semibold">Al</label>
                        <input type="date" name="to" id="to" value="{{ $filters['to'] }}" class="form-control">
                    </div>

                    <div class="col-12 col-lg-auto">
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
                                'defaultSort' => 'sent_at',
                            ])
                        </th>
                        <th>Recapito</th>
                        <th>
                            @include('components.crm.sortable-th', [
                                'label' => 'Stato',
                                'field' => 'status',
                                'defaultSort' => 'sent_at',
                            ])
                        </th>
                        <th>
                            @include('components.crm.sortable-th', [
                                'label' => 'Inviata',
                                'field' => 'sent_at',
                                'defaultSort' => 'sent_at',
                            ])
                        </th>
                        <th>
                            @include('components.crm.sortable-th', [
                                'label' => 'Scadenza',
                                'field' => 'expires_at',
                                'defaultSort' => 'sent_at',
                            ])
                        </th>
                        <th class="text-end crm-cell-end">Azioni</th>
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
                                <span class="text-break">{{ $row->contactPoint?->value ?? '—' }}</span>
                            </td>

                            <td>
                                <span class="crm-badge crm-badge--{{ $row->status_variant }}">
                                    {{ $row->status_label }}
                                </span>
                            </td>

                            <td>
                                <span class="text-nowrap">{{ $row->sent_at?->format('d/m/Y H:i') ?? 'Mai inviata' }}</span>
                            </td>

                            <td>
                                <span class="text-nowrap">{{ $row->expires_at?->format('d/m/Y H:i') ?? '—' }}</span>
                            </td>

                            <td class="text-end crm-cell-end">
                                @if($row->status !== 'completed')
                                    <form
                                        method="POST"
                                        action="{{ route('people.consent-requests.store', $row->owner_id) }}"
                                        class="d-inline"
                                    >
                                        @csrf
                                        <x-crm.button
                                            type="submit"
                                            icon="send"
                                            variant="outline-secondary"
                                            confirm="Inviare di nuovo la richiesta di consenso a questa persona?"
                                        >
                                            Reinvia
                                        </x-crm.button>
                                    </form>
                                @else
                                    <span class="crm-text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessuna richiesta trovata</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono richieste da mostrare con i filtri correnti.
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
