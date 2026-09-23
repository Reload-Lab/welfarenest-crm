@extends('layouts.app')

@php
    $indexRoute = 'logs.access';
@endphp

@section('content')
<div class="container-fluid">

    @include('logs.partials.tabs')

    <p class="crm-text-muted mb-4">
        Accessi al CRM e al portale WN+: entrate, uscite e tentativi falliti,
        con indirizzo IP e browser usato.
    </p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRoute) }}">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="event_type" class="form-label fw-semibold">Evento</label>
                        <select name="event_type" id="event_type" class="form-select">
                            <option value="">Tutti</option>
                            @foreach($eventTypes as $value => $label)
                                <option value="{{ $value }}" {{ $filters['event_type'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="ip" class="form-label fw-semibold">Indirizzo IP</label>
                        <input type="text" name="ip" id="ip" value="{{ $filters['ip'] }}" class="form-control">
                    </div>

                    @include('logs.partials.actor-date-filters')

                    <div class="col-12 col-lg">
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <x-crm.button type="submit" icon="search" variant="primary">Cerca</x-crm.button>
                            <x-crm.button href="{{ route($indexRoute) }}" icon="reset" variant="outline-secondary">
                                Reset
                            </x-crm.button>
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
                        <th>Evento</th>
                        <th>Chi</th>
                        <th>Indirizzo IP</th>
                        <th class="crm-cell-end">Browser</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        <tr class="crm-table__row">
                            <td class="crm-cell-start">
                                <span class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i:s') }}</span>
                            </td>

                            <td>
                                <span class="crm-badge crm-badge--{{ $log->event_variant }}">
                                    {{ $log->event_label }}
                                </span>
                            </td>

                            <td>{{ $log->actor_label }}</td>

                            <td><span class="crm-text-muted">{{ $log->ip_address ?? '—' }}</span></td>

                            <td class="crm-cell-end">
                                <span class="crm-text-muted small d-inline-block text-truncate" style="max-width: 22rem;">
                                    {{ $log->user_agent ?? '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessun accesso registrato</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono accessi da mostrare con i filtri correnti.
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
