<div class="card border-0 shadow-sm h-100">

    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0">Relazioni con organizzazioni</h3>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#personRelationModal"
        >
            Nuova relazione
        </button>
    </div>

    @foreach($person->organizationRelations as $relation)
    <div
        class="modal fade"
        id="editRelationModal-{{ $relation->id }}"
        tabindex="-1"
        aria-labelledby="editRelationModalLabel-{{ $relation->id }}"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title h5 mb-1" id="editRelationModalLabel-{{ $relation->id }}">
                            Modifica relazione
                        </h4>
                        <p class="text-muted small mb-0">
                            Aggiorna organizzazione, qualifica, dipartimento, periodo e stato della relazione.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>

                <div class="modal-body">
                    @include('people.partials.show.relation-form', [
                        'person' => $person,
                        'relation' => $relation,
                        'organizations' => $organizations,
                        'qualifications' => $qualifications,
                        'departments' => $departments,
                    ])
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="card-body p-4 d-flex flex-column gap-4">
        @if($person->organizationRelations->isEmpty())
            <div class="border rounded-3 p-4 bg-light-subtle">
                <p class="mb-2 fw-semibold">Nessuna relazione collegata</p>
                <p class="mb-0 text-muted">
                    Puoi aggiungere la prima relazione usando il pulsante "Nuova relazione".
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Organizzazione</th>
                            <th>Qualifica</th>
                            <th>Dipartimento</th>
                            <th>Periodo</th>
                            <th>Stato</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($person->organizationRelations as $relation)
                            <tr>
                                <td>{{ $relation->organization?->name ?: $relation->organization?->legal_name ?: '—' }}</td>
                                <td>{{ $relation->qualification?->name ?: '—' }}</td>
                                <td>{{ $relation->department?->name ?: '—' }}</td>
                                <td>
                                    @if($relation->start_date || $relation->end_date)
                                        {{ $relation->start_date?->format('d/m/Y') ?: '—' }}
                                        -
                                        {{ $relation->end_date?->format('d/m/Y') ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        @if($relation->is_active)
                                            <span class="badge text-bg-success">Attiva</span>
                                        @else
                                            <span class="badge text-bg-secondary">Non attiva</span>
                                        @endif

                                        @if($relation->is_primary)
                                            <span class="badge text-bg-light border text-dark">Principale</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editRelationModal-{{ $relation->id }}"
                                        onclick="event.preventDefault(); event.stopPropagation();"
                                    >
                                        Modifica
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div
    class="modal fade"
    id="personRelationModal"
    tabindex="-1"
    aria-labelledby="personRelationModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title h5 mb-1" id="personRelationModalLabel">
                        Nuova relazione
                    </h4>
                    <p class="text-muted small mb-0">
                        Collega la persona a un'organizzazione con qualifica, dipartimento, periodo e stato.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>

            <div class="modal-body">
                @include('people.partials.show.relation-form', [
                    'person' => $person,
                    'relation' => null,
                    'organizations' => $organizations,
                    'qualifications' => $qualifications,
                    'departments' => $departments,
                ])
            </div>
        </div>
    </div>
</div>


