@include('contact-points._section', [
    'owner' => $person,
    'ownerType' => 'person',
    'contactPoints' => $person->contactPoints,
    'storeRoute' => route('people.contact-points.store', $person),
    'destroyRouteName' => 'contact-points.destroy',
    'contactTypes' => $contactTypes,
    'contactUsages' => $contactUsages,
    'formIdPrefix' => 'person-contact-point',
    'collapseId' => 'person-contact-point-create',
    'errorBag' => 'storeContactPoint',
    'createLabel' => 'Nuovo recapito',
])