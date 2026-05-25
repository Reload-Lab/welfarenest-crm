<div class="card h-100 shadow-sm">
    <div class="card-header">
        <div>
            <h5>Note</h5>
        </div>

        <x-crm.icon-button
            icon="add"
            icon-group="actions"
            title="Nuova nota"
            data-bs-toggle="modal"
            data-bs-target="#noteCreateModal"
        />
       
    </div>


<div class="card-body">

    @include('notes._list', [
        'notes' => $featuredNote ? collect([$featuredNote]) : collect(),
        'compact' => true,
    ])

    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">

        <div class="d-flex gap-2 small text-muted">
            <span>{{ $activeNotes->count() }} attive</span>
            <span>•</span>
            <span>{{ $archivedNotes->count() }} archiviate</span>
        </div>

        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            data-bs-toggle="modal"
            data-bs-target="#notesModal"
        >
            Vedi tutte
        </button>

    </div>

</div>
</div>

<div class="modal fade" id="noteCreateModal" tabindex="-1" aria-labelledby="noteCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            @include('notes._form', [
                'action' => route('organizations.notes.store', $organization),
            ])
        </div>
    </div>
</div>

<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Note
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Chiudi"
                ></button>
            </div>

            <div class="modal-body">

                <ul class="nav nav-pills mb-4" role="tablist">

                    <li class="nav-item">
                        <button
                            class="nav-link active"
                            data-bs-toggle="pill"
                            data-bs-target="#notes-active"
                            type="button"
                        >
                            Attive
                            ({{ $activeNotes->count() }})
                        </button>
                    </li>

                    <li class="nav-item">
                        <button
                            class="nav-link"
                            data-bs-toggle="pill"
                            data-bs-target="#notes-archived"
                            type="button"
                        >
                            Archiviate
                            ({{ $archivedNotes->count() }})
                        </button>
                    </li>

                </ul>

                <div class="tab-content">

                    <div
                        class="tab-pane fade show active"
                        id="notes-active"
                    >

                        @include('notes._list', [
                            'notes' => $activeNotes,
                        ])

                    </div>

                    <div
                        class="tab-pane fade"
                        id="notes-archived"
                    >

                        @include('notes._list', [
                            'notes' => $archivedNotes,
                        ])

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>