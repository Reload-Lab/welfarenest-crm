<div class="card crm-card">
    <div class="card-header">
            <div>
                <h5>Recapiti</h5>
            </div>

            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuovo recapito"
                data-bs-toggle="modal"
                data-bs-target="#{{ $collapseId }}Modal"
            />
    </div>

    <div class="card-body">
        @include('contact-points._list', [
            'contactPoints' => $contactPoints,
            'destroyRouteName' => $destroyRouteName,
        ])
    </div>
</div>




<div class="modal fade"
     id="{{ $collapseId }}Modal"
     tabindex="-1"
     aria-labelledby="{{ $collapseId }}ModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $collapseId }}ModalLabel">
                    Nuovo recapito
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Chiudi"></button>
            </div>

            <div class="modal-body">
                @include('contact-points._form', [
                    'action' => $storeRoute,
                    'contactPoint' => null,
                    'contactTypes' => $contactTypes,
                    'contactUsages' => $contactUsages,
                    'formIdPrefix' => $formIdPrefix,
                    'errorBag' => $errorBag,
                ])
            </div>
        </div>
    </div>
</div>
