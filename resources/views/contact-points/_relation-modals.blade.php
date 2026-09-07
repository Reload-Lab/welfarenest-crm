{{--
    Modali dei recapiti di una relazione persona-organizzazione (creazione + modifica).

    Vanno inclusi FUORI dalla card della relazione (come elementi fratelli, es. subito dopo
    il ciclo che disegna le card), mai annidati dentro ".crm-relation-card": un modale Bootstrap
    annidato dentro un elemento con hover/transform smette di essere posizionato rispetto alla
    finestra e si "aggancia" alla card, causando uno sfarfallio continuo quando il mouse
    entra/esce dall'area coperta dal modale.

    Parametri attesi:
    - $relation: PersonOrganizationRelation
    - $contactPointsStoreRoute: route di salvataggio del nuovo recapito
    - $contactTypes, $contactUsages
    - $collapseId, $formIdPrefix, $errorBag: stessi valori passati a relation-contact-points
--}}

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
                    'action' => $contactPointsStoreRoute,
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

@foreach($relation->contactPoints as $contactPoint)
    <div class="modal fade"
         id="contactPointEditModal-{{ $contactPoint->id }}"
         tabindex="-1"
         aria-labelledby="contactPointEditModalLabel-{{ $contactPoint->id }}"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactPointEditModalLabel-{{ $contactPoint->id }}">
                        Modifica recapito
                    </h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Chiudi"></button>
                </div>

                <div class="modal-body">
                    @include('contact-points._form', [
                        'action' => route('contact-points.update', $contactPoint),
                        'method' => 'PUT',
                        'contactPoint' => $contactPoint,
                        'contactTypes' => $contactTypes,
                        'contactUsages' => $contactUsages,
                        'formIdPrefix' => 'contact-point-edit-' . $contactPoint->id,
                        'errorBag' => 'updateContactPoint',
                    ])
                </div>
            </div>
        </div>
    </div>
@endforeach
