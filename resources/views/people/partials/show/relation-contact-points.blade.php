@include('contact-points._section', [
    'owner' => $relation,
    'ownerType' => 'person_organization_relation',
    'contactPoints' => $relation->contactPoints,
    'storeRoute' => route('people.relations.contact-points.store', [$person, $relation]),
    'destroyRouteName' => 'contact-points.destroy',
    'contactTypes' => $contactTypes,
    'contactUsages' => $contactUsages,
    'formIdPrefix' => 'relation-contact-point-' . $relation->id,
    'collapseId' => 'relation-contact-point-create-' . $relation->id,
    'errorBag' => 'storeRelationContactPoint',
    'createLabel' => 'Nuovo recapito',
])