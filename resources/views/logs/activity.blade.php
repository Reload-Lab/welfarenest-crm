@extends('layouts.app')

@php
    $indexRoute = 'logs.activity';
    $hasAdvancedFilters = filled($filters['user_id']) || filled($filters['from']) || filled($filters['to']);
@endphp

@section('content')
<div class="container-fluid">

    @include('logs.partials.tabs')

    <p class="crm-text-muted mb-4">
        Le operazioni funzionali del sistema: cosa è stato fatto, non cosa è cambiato.
        Invio di richieste di consenso, consensi registrati, importazioni.
    </p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($indexRoute) }}">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="activity_type" class="form-label fw-semibold">Tipo di operazione</label>
                        <select name="activity_type" id="activity_type" class="form-select">
                            <option value="">Tutte</option>
                            @foreach($activityTypes as $value => $label)
                                <option value="{{ $value }}" {{ $filters['activity_type'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
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
                        <th>Operazione</th>
                        <th>Riferimento</th>
                        <th>Dettagli</th>
                        <th class="crm-cell-end">Autore</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        <tr class="crm-table__row">
                            <td class="crm-cell-start">
                                <span class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                            </td>

                            <td>{{ $log->activity_label }}</td>

                            <td>
                                @if($log->subject_type)
                                    @if($log->subject_url)
                                        <a href="{{ $log->subject_url }}" class="crm-entity-link">
                                            {{ $log->subject_label }} #{{ $log->subject_id }}
                                        </a>
                                    @else
                                        <span>{{ $log->subject_label }} #{{ $log->subject_id }}</span>
                                    @endif
                                @else
                                    <span class="crm-text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                @php $details = $log->details; @endphp

                                @if($details === [])
                                    <span class="crm-text-muted">—</span>
                                @else
                                    <div class="small crm-text-muted">
                                        @foreach($details as $key => $value)
                                            <div class="text-truncate" style="max-width: 28rem;">
                                                {{ \Illuminate\Support\Str::headline($key) }}:
                                                {{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <td class="crm-cell-end">{{ $log->actor_label }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessuna attività registrata</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono operazioni da mostrare con i filtri correnti.
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
