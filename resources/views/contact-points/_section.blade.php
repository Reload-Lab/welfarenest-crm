<div class="card crm-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="h5 mb-1">Recapiti</h2>
                <p class="text-muted small mb-0">
                    Email, telefoni, siti web e altri recapiti associati.
                </p>
            </div>

            <x-crm.button
                type="button"
                icon="add"
                data-bs-toggle="modal"
                data-bs-target="#{{ $collapseId }}Modal"
            >
                {{ $createLabel }}
            </x-crm.button>
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


<!--

        <div id="{{ $collapseId }}" class="collapse {{ $errors->hasBag($errorBag) ? 'show' : '' }}">
            <div class="border rounded-3 p-3 p-lg-4 mb-4 bg-body-tertiary">
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
-->



        @include('contact-points._list', [
            'contactPoints' => $contactPoints,
            'destroyRouteName' => $destroyRouteName,
        ])
    </div>
</div>