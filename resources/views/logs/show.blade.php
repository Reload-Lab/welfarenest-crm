@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        <x-crm.button href="{{ url()->previous() }}" icon="chevron-left" variant="outline-secondary">
            Torna al registro
        </x-crm.button>
    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-4">Operazione</h2>

                    <dl class="mb-0">
                        <dt class="crm-text-muted small">Data e ora</dt>
                        <dd class="mb-3">{{ $log->created_at?->format('d/m/Y H:i:s') }}</dd>

                        <dt class="crm-text-muted small">Tipo</dt>
                        <dd class="mb-3">
                            <span class="crm-badge crm-badge--{{ $log->event_variant }}">
                                {{ $log->event_label }}
                            </span>
                        </dd>

                        <dt class="crm-text-muted small">Entità</dt>
                        <dd class="mb-3">
                            @if($log->entity_url)
                                <a href="{{ $log->entity_url }}" class="crm-entity-link">
                                    {{ $log->entity_label }} #{{ $log->auditable_id }}
                                </a>
                            @else
                                {{ $log->entity_label }} #{{ $log->auditable_id }}
                            @endif
                        </dd>

                        <dt class="crm-text-muted small">Autore</dt>
                        <dd class="mb-3">{{ $log->actor_label }}</dd>

                        @if($log->origin_label)
                            <dt class="crm-text-muted small">Origine</dt>
                            <dd class="mb-3">{{ $log->origin_label }}</dd>
                        @endif

                        @if($ip = ($log->context_json['ip'] ?? null))
                            <dt class="crm-text-muted small">Indirizzo IP</dt>
                            <dd class="mb-3">{{ $ip }}</dd>
                        @endif

                        @if($route = ($log->context_json['route'] ?? null))
                            <dt class="crm-text-muted small">Pagina</dt>
                            <dd class="mb-0"><span class="crm-text-muted small">{{ $route }}</span></dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="crm-table-card">
                <div class="crm-table-responsive">
                    <table class="table crm-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="crm-cell-start">Campo</th>
                                <th>Valore precedente</th>
                                <th class="crm-cell-end">Valore nuovo</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($log->change_list as $change)
                                <tr class="crm-table__row">
                                    <td class="crm-cell-start fw-semibold">{{ $change['label'] }}</td>
                                    <td><span class="crm-text-muted">{{ $change['old'] }}</span></td>
                                    <td class="crm-cell-end">{{ $change['new'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-0">
                                        <div class="crm-empty-state">
                                            <h3 class="crm-empty-state__title">Nessun valore registrato</h3>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
