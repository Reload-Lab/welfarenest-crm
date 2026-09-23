@extends('layouts.app')

@php
    $indexRoute = 'logs.audit';
    $hasAdvancedFilters = filled($filters['entity_type']) || filled($filters['event_type'])
        || filled($filters['user_id']) || filled($filters['from']) || filled($filters['to']);
@endphp

@section('content')
<div class="container-fluid">

    @include('logs.partials.tabs')

    <p class="crm-text-muted mb-4">
        Ogni creazione, modifica e cancellazione delle entità tracciate, con i valori
        precedenti e quelli nuovi. Il registro è di sola lettura: le righe non si
        modificano e non si cancellano.
    </p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRoute) }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg">
                        <label for="search" class="form-label fw-semibold">Ricerca nei valori</label>
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
                                placeholder="Un valore prima o dopo la modifica: un'email, un nome, una partita IVA..."
                            >
                        </div>
                    </div>

                    <div class="col-12 col-lg-auto">
                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-inline"
                                id="toggleLogFilters"
                                aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                                aria-controls="logAdvancedFilters"
                            >
                                <x-icon group="actions" name="sliders" />
                                <span>Filtri</span>
                            </button>

                            <x-crm.button type="submit" icon="search" variant="primary">Cerca</x-crm.button>
                        </div>
                    </div>
                </div>

                <div id="logAdvancedFilters" class="mt-3 {{ $hasAdvancedFilters ? '' : 'd-none' }}">
                    <div class="crm-filters-panel">
                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-md-4 col-lg-3">
                                <label for="entity_type" class="form-label fw-semibold">Entità</label>
                                <select name="entity_type" id="entity_type" class="form-select">
                                    <option value="">Tutte</option>
                                    @foreach($entityTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $filters['entity_type'] === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2">
                                <label for="event_type" class="form-label fw-semibold">Operazione</label>
                                <select name="event_type" id="event_type" class="form-select">
                                    <option value="">Tutte</option>
                                    @foreach($eventTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $filters['event_type'] === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @include('logs.partials.actor-date-filters')

                            <div class="col-12 col-lg">
                                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <x-crm.button href="{{ route($indexRoute) }}" icon="reset" variant="outline-secondary">
                                        Reset
                                    </x-crm.button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <input type="hidden" name="per_page" value="{{ $filters['per_page'] }}">
            </form>
        </div>
    </div>

    <div class="crm-table-card">
        <div class="crm-table-responsive">
            <table class="table crm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="crm-cell-start">Data e ora</th>
                        <th>Operazione</th>
                        <th>Entità</th>
                        <th>Modifiche</th>
                        <th>Autore</th>
                        <th class="text-end crm-cell-end">Azioni</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        @php $changes = $log->change_list; @endphp
                        <tr class="crm-table__row">
                            <td class="crm-cell-start">
                                <span class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                            </td>

                            <td>
                                <span class="crm-badge crm-badge--{{ $log->event_variant }}">
                                    {{ $log->event_label }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($icon = \App\Support\AuditPresenter::entityIcon($log->auditable_type))
                                        <x-icon :group="$icon['group']" :name="$icon['name']" class="crm-text-muted" />
                                    @endif

                                    @if($log->entity_url)
                                        <a href="{{ $log->entity_url }}" class="crm-entity-link">
                                            {{ $log->entity_label }} #{{ $log->auditable_id }}
                                        </a>
                                    @else
                                        <span>{{ $log->entity_label }} #{{ $log->auditable_id }}</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($changes === [])
                                    <span class="crm-text-muted">—</span>
                                @else
                                    <div class="small">
                                        @foreach(array_slice($changes, 0, 2) as $change)
                                            <div class="text-truncate" style="max-width: 32rem;">
                                                <span class="fw-semibold">{{ $change['label'] }}:</span>
                                                <span class="crm-text-muted">{{ $change['old'] }}</span>
                                                &rarr;
                                                <span>{{ $change['new'] }}</span>
                                            </div>
                                        @endforeach

                                        @if(count($changes) > 2)
                                            <span class="crm-text-muted">
                                                e altri {{ count($changes) - 2 }} campi
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div>{{ $log->actor_label }}</div>
                                @if(! $log->user && $log->origin_label)
                                    <span class="crm-text-muted small">{{ $log->origin_label }}</span>
                                @endif
                            </td>

                            <td class="text-end crm-cell-end">
                                <x-crm.icon-button
                                    icon="view"
                                    icon-group="actions"
                                    title="Dettaglio"
                                    href="{{ route('logs.audit.show', $log) }}"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessuna modifica registrata</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono modifiche da mostrare con i filtri correnti.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('logs.partials.footer')
    </div>

</div>
@endsection
