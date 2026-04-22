@include('contact-points._section', [
    'owner' => $organization,
    'ownerType' => 'organization',
    'contactPoints' => $organization->contactPoints,
    'storeRoute' => route('organizations.contact-points.store', $organization),
    'destroyRouteName' => 'contact-points.destroy',
    'contactTypes' => $contactTypes,
    'contactUsages' => $contactUsages,
    'formIdPrefix' => 'organization-contact-point',
    'collapseId' => 'organization-contact-point-create',
    'errorBag' => 'storeContactPoint',
])