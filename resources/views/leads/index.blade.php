@extends('layouts.app')

@php
    $hasAdvancedFilters = filled($statusId) || filled($sourceId) || filled($assignedUserId) || $onlyOpen;
@endphp

@section('topbar_title', 'Lead')
@section('topbar_subtitle', 'Contatti commerciali non ancora in anagrafica')

@section('pageHeader')

    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            {{-- titolo / breadcrumb / altro --}}
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuovo lead"
                href="{{ route('leads.create') }}"
            />
        </div>
    </div>

@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('leads.index') }}">
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
                            value="{{ $search }}"
                            class="form-control crm-filter-search-input"
                            placeholder="Titolo, nome, cognome o azienda"
                        >
                    </div>
                </div>

                <div class="col-12 col-lg-auto">
                    <div class="d-flex flex-wrap gap-2">
                        {{-- Collapse nativo di Bootstrap: evita di dover aggiungere
                             un handler in app.js e ricompilare gli asset. --}}
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-inline"
                            data-bs-toggle="collapse"
                            data-bs-target="#leadsAdvancedFilters"
                            aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                            aria-controls="leadsAdvancedFilters"
                        >
                            <x-icon group="actions" name="sliders" />
                            <span>Filtri</span>
                        </button>

                        <x-crm.button
                            type="submit"
                            icon="search"
                            variant="primary"
                        >
                            Cerca
                        </x-crm.button>
                    </div>
                </div>
            </div>

            <div
                id="leadsAdvancedFilters"
                class="collapse mt-3 {{ $hasAdvancedFilters ? 'show' : '' }}"
            >
                <div class="crm-filters-panel">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="lead_status_id" class="form-label fw-semibold">Stato</label>
                            <select name="lead_status_id" id="lead_status_id" class="form-select">
                                <option value="">Tutti</option>
                                @foreach($statuses as $status)
                                    <option
                                        value="{{ $status->id }}"
                                        {{ (string) $statusId === (string) $status->id ? 'selected' : '' }}
                                    >
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="lead_source_id" class="form-label fw-semibold">Fonte</label>
                            <select name="lead_source_id" id="lead_source_id" class="form-select">
                                <option value="">Tutte</option>
                                @foreach($sources as $source)
                                    <option
                                        value="{{ $source->id }}"
                                        {{ (string) $sourceId === (string) $source->id ? 'selected' : '' }}
                                    >
                                        {{ $source->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="assigned_user_id" class="form-label fw-semibold">Assegnato a</label>
                            <select name="assigned_user_id" id="assigned_user_id" class="form-select">
                                <option value="">Tutti</option>
                                @foreach($users as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        {{ (string) $assignedUserId === (string) $user->id ? 'selected' : '' }}
                                    >
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <div class="form-check form-switch mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    name="only_open"
                                    id="only_open"
                                    value="1"
                                    {{ $onlyOpen ? 'checked' : '' }}
                                >
                                <label class="form-check-label fw-semibold" for="only_open">
                                    Solo lead aperti
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <x-crm.button
                                    href="{{ route('leads.index') }}"
                                    icon="reset"
                                    variant="outline-secondary"
                                >
                                    Reset
                                </x-crm.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
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
                            'label' => 'Lead',
                            'field' => 'name',
                            'defaultSort' => 'created_at',
                        ])
                    </th>

                    <th>
                        @include('components.crm.sortable-th', [
                            'label' => 'Azienda',
                            'field' => 'company_name',
                            'defaultSort' => 'created_at',
                        ])
                    </th>

                    <th>Stato</th>
                    <th>Fonte</th>
                    <th>Assegnato a</th>

                    <th class="text-end">
                        @include('components.crm.sortable-th', [
                            'label' => 'Chiusura prevista',
                            'field' => 'expected_close_date',
                            'defaultSort' => 'created_at',
                        ])
                    </th>

                    <th class="text-end crm-cell-end">Azioni</th>
                </tr>
            </thead>

            <tbody>
                @forelse($leads as $lead)
                    <tr class="crm-table__row">
                        <td class="crm-cell-start">
                            <div class="min-w-0">
                                <a href="{{ route('leads.show', $lead) }}"
                                   class="crm-entity-link d-inline-block text-truncate">
                                    {{ $lead->display_name }}
                                </a>

                                @if($lead->full_name !== '' && $lead->name !== $lead->full_name)
                                    <div class="crm-text-muted small text-truncate">{{ $lead->name }}</div>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($lead->organization)
                                <a href="{{ route('organizations.show', $lead->organization) }}" class="crm-entity-link">
                                    {{ $lead->organization->display_name }}
                                </a>
                            @else
                                <span class="crm-text-muted">{{ $lead->company_name ?: '—' }}</span>
                            @endif
                        </td>

                        <td>
                            <span class="crm-badge crm-badge--muted">
                                {{ $lead->status?->name ?? '—' }}
                            </span>
                        </td>

                        <td>
                            <span class="crm-text-muted">{{ $lead->source?->name ?? '—' }}</span>
                        </td>

                        <td>
                            <span class="crm-text-muted">{{ $lead->assignedUser?->name ?? '—' }}</span>
                        </td>

                        <td class="text-end">
                            <span class="crm-text-muted">
                                {{ $lead->expected_close_date?->format('d/m/Y') ?? '—' }}
                            </span>
                        </td>

                        <td class="text-end crm-cell-end">
                            @include('components.crm.row-actions', [
                                'edit' => route('leads.edit', $lead),
                                'delete' => route('leads.destroy', $lead),
                                'deleteConfirm' => 'Confermi l\'eliminazione di questo lead?',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-0">
                            <div class="crm-empty-state">
                                <div class="crm-empty-state__icon">
                                    <x-icon group="actions" name="search" />
                                </div>
                                <h3 class="crm-empty-state__title">Nessun lead trovato</h3>
                                <p class="crm-empty-state__text">
                                    Non ci sono lead da mostrare con i filtri correnti.
                                </p>

                                <div class="mt-3">
                                    <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                                        Crea il primo lead
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer crm-table-footer">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <form method="GET" action="{{ route('leads.index') }}" class="crm-table-footer__left">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="lead_status_id" value="{{ $statusId }}">
                <input type="hidden" name="lead_source_id" value="{{ $sourceId }}">
                <input type="hidden" name="assigned_user_id" value="{{ $assignedUserId }}">
                <input type="hidden" name="only_open" value="{{ $onlyOpen ? 1 : '' }}">

                <select
                    name="per_page"
                    id="per_page_footer"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()"
                >
                    <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10 righe</option>
                    <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20 righe</option>
                    <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50 righe</option>
                </select>
            </form>

            <div class="crm-table-footer__right">
                @if($leads->hasPages())
                    <div class="crm-pagination">
                        {{ $leads->links() }}
                    </div>
                @else
                    <span class="crm-text-muted small">
                        {{ $leads->total() }} risultati trovati
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
