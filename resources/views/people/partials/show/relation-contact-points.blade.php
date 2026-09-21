{{--
    Righe dei recapiti della relazione, senza il box/header "Recapiti della relazione":
    vivono direttamente dentro la card della persona/organizzazione (vedi relations.blade.php
    e organizations/partials/show/people.blade.php). Il pulsante "+" per aggiungerne uno è nel
    menu tre puntini della relazione ("Aggiungi recapito", vedi row-actions.blade.php); il modale
    di creazione/modifica resta quello reso da contact-points._relation-modals.
--}}
@include('contact-points._list', [
    'contactPoints' => $relation->contactPoints,
    'destroyRouteName' => 'contact-points.destroy',
    'renderModals' => false,
])