<div class="card border-0 shadow-sm">
    @php
        $editingRelationId = (int) old('relation_id');
        $shouldOpenCreateRelationModal = $errors->any() && ! $editingRelationId;
    @endphp

    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0">Persone</h3>

        <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            data-bs-toggle="modal"
            data-bs-target="#organizationRelationModal"
        >
            Aggiungi persona
        </button>
    </div>

    <div class="card-body p-4">
        @if($organization->personOrganizationRelations->isEmpty())
            <div class="border rounded-3 p-4 bg-light-subtle">
                <p class="mb-2 fw-semibold">Nessuna persona collegata</p>
                <p class="mb-0 text-muted">
                    Non risultano ancora relazioni associate a questa organizzazione.
                </p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th>Qualifica</th>
                            <th>Dipartimento</th>
                            <th>Periodo</th>
                            <th>Stato</th>
                            <th class="text-end">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($organization->personOrganizationRelations as $relation)
                            <tr>
                                <td>
                                    @if($relation->person)

                                    <x-crm.avatar
                                        :name="$relation->person->display_name"
                                        :image="$relation->person->avatar_url"
                                        type="person"
                                        size="sm"
                                    />

                                        <a
                                            href="{{ route('people.show', $relation->person) }}"
                                            class="text-decoration-none fw-semibold"
                                        >
                                            {{ $relation->person->full_name ?: '-' }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <x-crm.tag
                                        :label="$relation->qualification?->name"
                                        icon-group="entities"
                                        icon-name="qualification"
                                    />
                                </td>
                                <td>
                                    <x-crm.tag
                                        :label="$relation->department?->name"
                                        icon-group="entities"
                                        icon-name="department"
                                    /> 
                                </td>
                                <td>
                                    @if($relation->start_date || $relation->end_date)
                                        {{ $relation->start_date?->format('d/m/Y') ?: '-' }}
                                        -
                                        {{ $relation->end_date?->format('d/m/Y') ?: '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($relation->is_active)
                                        <span class="badge text-bg-success">Attiva</span>
                                    @else
                                        <span class="badge text-bg-secondary">Non attiva</span>
                                    @endif
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
    id="organizationRelationModal"
    tabindex="-1"
    aria-labelledby="organizationRelationModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title h5 mb-1" id="organizationRelationModalLabel">
                        Aggiungi persona
                    </h4>
                    <p class="text-muted small mb-0">
                        Collega una persona a questa organizzazione con qualifica, dipartimento, periodo e stato.
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>

            <div class="modal-body">
                @include('people.partials.show.relation-form', [
                    'relation' => null,
                    'relationContext' => 'organization',
                    'selectedOrganization' => $organization,
                    'selectedPerson' => $selectedPerson,
                    'qualifications' => $qualifications,
                    'departments' => $departments,
                ])
            </div>
        </div>
    </div>
</div>

@foreach($organization->personOrganizationRelations as $relation)
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
                            Aggiorna qualifica, dipartimento, periodo e stato della relazione.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>

                <div class="modal-body">
                    @include('people.partials.show.relation-form', [
                        'relation' => $relation,
                        'relationContext' => 'organization',
                        'selectedOrganization' => $organization,
                        'selectedPerson' => $relation->person,
                        'qualifications' => $qualifications,
                        'departments' => $departments,
                    ])
                </div>
            </div>
        </div>
    </div>
@endforeach

@if($shouldOpenCreateRelationModal || $editingRelationId)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalId = @json($editingRelationId ? 'editRelationModal-' . $editingRelationId : 'organizationRelationModal');
            var modalElement = document.getElementById(modalId);

            if (!modalElement || typeof bootstrap === 'undefined') {
                return;
            }

            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    </script>
@endif
