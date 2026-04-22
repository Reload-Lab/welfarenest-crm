<div class="card crm-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h2 class="h5 mb-1">Recapiti</h2>
                <p class="text-muted small mb-0">
                    Email, telefoni, siti web e altri recapiti associati.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $collapseId }}"
                aria-expanded="{{ $errors->hasBag($errorBag) ? 'true' : 'false' }}"
                aria-controls="{{ $collapseId }}"
            >
                <x-icon group="actions" name="plus" class="me-1" />
                Nuovo recapito
            </button>
        </div>

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

        @include('contact-points._list', [
            'contactPoints' => $contactPoints,
            'destroyRouteName' => $destroyRouteName,
        ])
    </div>
</div>