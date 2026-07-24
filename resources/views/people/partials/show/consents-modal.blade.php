@php
    $modalId = $modalId ?? 'personConsentsModal';
    $modalOwner = $owner ?? (isset($person) ? $person : null);
@endphp

<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Privacy e consensi
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @include('people.partials.show.consents', [
                    'owner' => $modalOwner,
                ])

            </div>

        </div>
    </div>

</div>