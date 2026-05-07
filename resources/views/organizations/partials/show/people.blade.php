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
            <div class="row g-3">
                @foreach($organization->personOrganizationRelations as $relation)
                    <div class="col-12 col-lg-6">
                        <div class="crm-relation-card h-100">
                            <div class="d-flex align-items-start gap-3">
                                @if($relation->person)
                                    <x-crm.avatar
                                        :name="$relation->person->display_name"
                                        :image="$relation->person->avatar_url"
                                        type="person"
                                        size="sm"
                                    />
                                @endif

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            @if($relation->person)
                                                <a
                                                    href="{{ route('people.show', $relation->person) }}"
                                                    class="crm-relation-card__name"
                                                >
                                                    {{ $relation->person->full_name ?: '-' }}
                                                </a>
                                            @else
                                                <span class="crm-relation-card__name">Persona non disponibile</span>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                            <x-crm.status
                                                :label="$relation->is_active ? 'Attiva' : 'Non attiva'"
                                                :variant="$relation->is_active ? 'success' : 'muted'"
                                                icon-group="status"
                                                :icon-name="$relation->is_active ? 'active' : 'inactive'"
                                                mode="icon"
                                            />

                                            @include('components.crm.row-actions', [
                                                'editModalTarget' => '#editRelationModal-' . $relation->id,
                                                'delete' => route('organizations.relations.destroy', [$organization, $relation]),
                                                'deleteConfirm' => 'Confermi l\'eliminazione di questa relazione?',
                                            ])
                                        </div>
                                    </div>

                                    <div class="crm-relation-card__tags mt-2">
                                        <x-crm.tag
                                            :label="$relation->qualification?->name"
                                            icon-group="entities"
                                            icon-name="qualification"
                                        />

                                        <x-crm.tag
                                            :label="$relation->department?->name"
                                            icon-group="entities"
                                            icon-name="department"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
