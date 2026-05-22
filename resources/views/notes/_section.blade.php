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
        @include('notes._list', ['notes' => $notes])
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