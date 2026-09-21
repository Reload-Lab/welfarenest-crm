<?php

use App\Models\Address;
use App\Models\AddressType;
use App\Models\ContactPoint;
use App\Models\ContactType;
use App\Models\ContactUsage;
use App\Models\Department;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Organization;
use App\Models\OrganizationRole;
use App\Models\OrganizationType;
use App\Models\PersonOrganizationRelation;
use App\Models\Qualification;

/*
|--------------------------------------------------------------------------
| Anagrafiche di base (tabelle di classificazione)
|--------------------------------------------------------------------------
|
| Ogni voce descrive una "tabella di classificazione" (code, name,
| description, is_active, sort_order) gestibile dal modulo generico
| Anagrafiche (TaxonomyController). Per aggiungere una nuova tabella
| basta aggiungere una voce qui: non serve un controller o una vista
| dedicati.
|
| - locked: se true, la tabella è di sistema. Viene mostrata in sola
|   lettura (nessuna creazione, modifica, disattivazione o eliminazione
|   possibile da questa sezione).
| - usage: closure che, dato l'id di una riga, restituisce quanti record
|   la referenziano altrove. Se > 0, l'eliminazione viene bloccata e si
|   propone la disattivazione al suo posto.
| - extra_fields: campi oltre ai 5 standard (es. la "category" di
|   contact_types). Supporta i tipi 'text' e 'select'.
|
*/

return [

    'organization-types' => [
        'model' => OrganizationType::class,
        'label' => 'Tipologia organizzazione',
        'label_plural' => 'Tipologie organizzazione',
        'description' => "Classifica la natura dell'organizzazione (azienda, fondo, banca, studio professionale, ecc.).",
        'locked' => false,
        'icon' => ['group' => 'entities', 'name' => 'organization'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => Organization::where('organization_type_id', $id)->count(),
        'usage_label' => 'organizzazioni',
    ],

    'organization-roles' => [
        'model' => OrganizationRole::class,
        'label' => 'Ruolo organizzazione',
        'label_plural' => 'Ruoli organizzazione',
        'description' => 'Definisce il rapporto commerciale con Welfare Nest (cliente, fornitore, interno). Valori strutturali, non modificabili da qui.',
        'locked' => true,
        'icon' => ['group' => 'entities', 'name' => 'client'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => OrganizationRole::query()->find($id)?->organizations()->count() ?? 0,
        'usage_label' => 'organizzazioni',
    ],

    'qualifications' => [
        'model' => Qualification::class,
        'label' => 'Qualifica',
        'label_plural' => 'Qualifiche',
        'description' => "Ruolo ricoperto da una persona all'interno di un'organizzazione (presidente, consulente, direttore, ecc.).",
        'locked' => false,
        'icon' => ['group' => 'entities', 'name' => 'qualification'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => PersonOrganizationRelation::where('qualification_id', $id)->count(),
        'usage_label' => 'relazioni persona-organizzazione',
    ],

    'departments' => [
        'model' => Department::class,
        'label' => 'Dipartimento',
        'label_plural' => 'Dipartimenti',
        'description' => 'Area o funzione aziendale a cui è assegnata una relazione persona-organizzazione.',
        'locked' => false,
        'icon' => ['group' => 'entities', 'name' => 'department'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => PersonOrganizationRelation::where('department_id', $id)->count(),
        'usage_label' => 'relazioni persona-organizzazione',
    ],

    'contact-types' => [
        'model' => ContactType::class,
        'label' => 'Tipo contatto',
        'label_plural' => 'Tipi contatto',
        'description' => "Tipologia di recapito (email, PEC, telefono, sito, social...). La categoria determina la validazione e l'icona applicate al recapito.",
        'locked' => false,
        'icon' => ['group' => 'contact', 'name' => 'contact_point'],
        'extra_fields' => [
            'category' => [
                'label' => 'Categoria',
                'type' => 'select',
                'options' => [
                    'email' => 'Email',
                    'phone' => 'Telefono',
                    'social' => 'Social',
                    'web' => 'Web',
                ],
                'required' => true,
            ],
        ],
        'usage' => fn (int $id): int => ContactPoint::where('contact_type_id', $id)->count(),
        'usage_label' => 'recapiti',
    ],

    'contact-usages' => [
        'model' => ContactUsage::class,
        'label' => 'Utilizzo contatto',
        'label_plural' => 'Utilizzi contatto',
        'description' => "Contesto d'uso di un recapito (personale, lavorativo, amministrativo, ecc.).",
        'locked' => false,
        'icon' => ['group' => 'contact', 'name' => 'contact_point'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => ContactPoint::where('contact_usage_id', $id)->count(),
        'usage_label' => 'recapiti',
    ],

    'address-types' => [
        'model' => AddressType::class,
        'label' => 'Tipo indirizzo',
        'label_plural' => 'Tipi indirizzo',
        'description' => 'Tipologia di indirizzo (sede legale, operativa, residenza, spedizione, ecc.).',
        'locked' => false,
        'icon' => ['group' => 'contact', 'name' => 'address'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => Address::where('address_type_id', $id)->count(),
        'usage_label' => 'indirizzi',
    ],

    'lead-statuses' => [
        'model' => LeadStatus::class,
        'label' => 'Stato lead',
        'label_plural' => 'Stati lead',
        'description' => "Fasi della pipeline commerciale di un lead. Da qui si gestiscono nome, ordine e attivazione; l'esito finale (vinto/perso) è strutturale e non è modificabile da questa sezione.",
        'locked' => false,
        'icon' => ['group' => 'entities', 'name' => 'lead'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => Lead::where('lead_status_id', $id)->count(),
        'usage_label' => 'lead',
    ],

    'lead-sources' => [
        'model' => LeadSource::class,
        'label' => 'Fonte lead',
        'label_plural' => 'Fonti lead',
        'description' => 'Canale da cui è arrivato il contatto (sito, convegni, newsletter, segnalazione, contatto diretto).',
        'locked' => false,
        'icon' => ['group' => 'entities', 'name' => 'lead'],
        'extra_fields' => [],
        'usage' => fn (int $id): int => Lead::where('lead_source_id', $id)->count(),
        'usage_label' => 'lead',
    ],

];
