@php
    $owner = $owner ?? (isset($person) ? $person : null);
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h6 mb-0">Privacy e consensi</h3>
        </div>

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