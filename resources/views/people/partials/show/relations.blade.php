<div class="card border-0 shadow-sm">

    <div class="card-header">
        <div>
            <h5>Relazioni</h5>
        </div>

            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuova relazione"
                class="crm-tooltip"
                data-bs-toggle="modal"
                data-bs-target="#personRelationModal"
            />
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


<div class="row g-3">
    @foreach($person->organizationRelations as $relation)
        <div class="col-12">
            <div class="crm-relation-card h-100">
                <div class="d-flex align-items-start gap-3">
                    @if($relation->organization)
                        <x-crm.avatar
                            :name="$relation->organization->display_name"
                            :image="$relation->organization->avatar_url"
                            type="organization"
                            size="sm"
                        />
                    @endif

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-w-0">
                                @if($relation->organization)
                                    <a
                                        href="{{ route('organizations.show', $relation->organization) }}"
                                        class="crm-relation-card__name"
                                    >
                                        {{ $relation->organization->display_name }}
                                    </a>
                                @else
                                    <span class="crm-relation-card__name">Organizzazione non disponibile</span>
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
                                    'delete' => route('people.relations.destroy', [$person, $relation]),
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

                            @if($relation->is_primary)
                                <x-crm.tag
                                    label="Principale"
                                    icon-group="status"
                                    icon-name="star"
                                />
                            @endif
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


