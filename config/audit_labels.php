<?php

use App\Models\ContactType;
use App\Models\ContactUsage;
use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Person;
use App\Models\Qualification;
use App\Models\User;
use App\Models\WnPlusLevel;
use App\Models\WnPlusRole;

/**
 * Vocabolario della sezione Registri.
 *
 * Traduce in italiano cio che le tabelle di log contengono in forma tecnica:
 * il tipo di entita, il nome dei campi, il tipo di evento e l'origine.
 * Un campo non elencato qui viene mostrato con il suo nome tecnico, quindi
 * l'elenco puo crescere per gradi senza rompere nulla.
 */
return [

    /*
     | Entita tracciate. 'route' e la rotta di dettaglio a cui collegare la
     | riga di log (null = nessun link); riceve l'id dell'entita.
     */
    'entities' => [
        'organization' => [
            'label' => 'Organizzazione',
            'label_plural' => 'Organizzazioni',
            'icon' => ['group' => 'entities', 'name' => 'organization'],
            'route' => 'organizations.show',
        ],
        'person' => [
            'label' => 'Persona',
            'label_plural' => 'Persone',
            'icon' => ['group' => 'entities', 'name' => 'person'],
            'route' => 'people.show',
        ],
        'person_organization_relation' => [
            'label' => 'Relazione',
            'label_plural' => 'Relazioni',
            'icon' => ['group' => 'entities', 'name' => 'relation'],
            'route' => null,
        ],
        'contact_point' => [
            'label' => 'Recapito',
            'label_plural' => 'Recapiti',
            'icon' => ['group' => 'contact', 'name' => 'contact_point'],
            'route' => null,
        ],
        'consent' => [
            'label' => 'Consenso',
            'label_plural' => 'Consensi',
            'icon' => ['group' => 'entities', 'name' => 'consent'],
            'route' => null,
        ],
        'wn_plus_account' => [
            'label' => 'Account WN+',
            'label_plural' => 'Account WN+',
            'icon' => ['group' => 'entities', 'name' => 'welfarenestplus'],
            'route' => 'wn-plus.accounts.show',
        ],
        // Soggetti che compaiono solo nell'activity log
        'consent_request' => [
            'label' => 'Richiesta di consenso',
            'label_plural' => 'Richieste di consenso',
            'icon' => ['group' => 'entities', 'name' => 'consent'],
            'route' => null,
        ],
        'import_batch' => [
            'label' => 'Importazione',
            'label_plural' => 'Importazioni',
            'icon' => ['group' => 'navigation', 'name' => 'reports'],
            'route' => null,
        ],
    ],

    /*
     | Etichette dei campi. La chiave '*' vale per tutte le entita; una voce
     | specifica dell'entita ha la precedenza.
     |
     | 'type' determina come viene formattato il valore:
     |   boolean | date | datetime | lookup (con 'model' e 'attribute')
     */
    'fields' => [

        '*' => [
            'is_active' => ['label' => 'Attivo', 'type' => 'boolean'],
            'is_primary' => ['label' => 'Principale', 'type' => 'boolean'],
            'notes' => ['label' => 'Note'],
            'label' => ['label' => 'Etichetta'],
            'status' => ['label' => 'Stato'],
            'source' => ['label' => 'Origine'],
            'owner_type' => ['label' => 'Tipo di riferimento'],
            'owner_id' => ['label' => 'Riferimento'],
            'created_by_user_id' => ['label' => 'Creato da', 'type' => 'lookup', 'model' => User::class, 'attribute' => 'name'],
        ],

        'organization' => [
            'name' => ['label' => 'Nome'],
            'legal_name' => ['label' => 'Ragione sociale'],
            'organization_type_id' => ['label' => 'Tipo organizzazione', 'type' => 'lookup', 'model' => OrganizationType::class, 'attribute' => 'name'],
            'vat_number' => ['label' => 'Partita IVA'],
            'tax_code' => ['label' => 'Codice fiscale'],
            'sdi_code' => ['label' => 'Codice SDI'],
            'is_split_payment' => ['label' => 'Split payment', 'type' => 'boolean'],
            'avatar_path' => ['label' => 'Logo'],
        ],

        'person' => [
            'first_name' => ['label' => 'Nome'],
            'last_name' => ['label' => 'Cognome'],
            'avatar_path' => ['label' => 'Foto'],
        ],

        'person_organization_relation' => [
            'person_id' => ['label' => 'Persona', 'type' => 'lookup', 'model' => Person::class, 'attribute' => 'full_name'],
            'organization_id' => ['label' => 'Organizzazione', 'type' => 'lookup', 'model' => Organization::class, 'attribute' => 'display_name'],
            'qualification_id' => ['label' => 'Qualifica', 'type' => 'lookup', 'model' => Qualification::class, 'attribute' => 'name'],
            'department_id' => ['label' => 'Reparto', 'type' => 'lookup', 'model' => Department::class, 'attribute' => 'name'],
            'start_date' => ['label' => 'Data inizio', 'type' => 'date'],
            'end_date' => ['label' => 'Data fine', 'type' => 'date'],
        ],

        'contact_point' => [
            'contact_type_id' => ['label' => 'Tipo di recapito', 'type' => 'lookup', 'model' => ContactType::class, 'attribute' => 'name'],
            'contact_usage_id' => ['label' => 'Uso del recapito', 'type' => 'lookup', 'model' => ContactUsage::class, 'attribute' => 'name'],
            'value' => ['label' => 'Recapito'],
        ],

        'consent' => [
            'consent_type_id' => ['label' => 'Tipo di consenso'],
            'consent_version_id' => ['label' => 'Versione informativa'],
            'requested_at' => ['label' => 'Richiesto il', 'type' => 'datetime'],
            'granted_at' => ['label' => 'Concesso il', 'type' => 'datetime'],
            'revoked_at' => ['label' => 'Revocato il', 'type' => 'datetime'],
            'denied_at' => ['label' => 'Negato il', 'type' => 'datetime'],
            'evidence_file_path' => ['label' => 'Prova allegata'],
        ],

        'wn_plus_account' => [
            'uuid' => ['label' => 'UUID'],
            'organization_id' => ['label' => 'Organizzazione', 'type' => 'lookup', 'model' => Organization::class, 'attribute' => 'display_name'],
            'person_id' => ['label' => 'Persona collegata', 'type' => 'lookup', 'model' => Person::class, 'attribute' => 'full_name'],
            'first_name' => ['label' => 'Nome'],
            'last_name' => ['label' => 'Cognome'],
            'email' => ['label' => 'Email'],
            'wn_plus_role_id' => ['label' => 'Ruolo', 'type' => 'lookup', 'model' => WnPlusRole::class, 'attribute' => 'name'],
            'wn_plus_level_id' => ['label' => 'Livello', 'type' => 'lookup', 'model' => WnPlusLevel::class, 'attribute' => 'name'],
            'account_type' => ['label' => 'Tipo di account'],
            'email_verified_at' => ['label' => 'Email verificata il', 'type' => 'datetime'],
            'last_login_at' => ['label' => 'Ultimo accesso', 'type' => 'datetime'],
            'invited_by_account_id' => ['label' => 'Invitato da'],
        ],
    ],

    'events' => [
        'created' => ['label' => 'Creazione', 'variant' => 'success'],
        'updated' => ['label' => 'Modifica', 'variant' => 'info'],
        'deleted' => ['label' => 'Cancellazione', 'variant' => 'danger'],
        'restored' => ['label' => 'Ripristino', 'variant' => 'warning'],
    ],

    'origins' => [
        'web' => 'Interfaccia CRM',
        'console' => 'Riga di comando',
        'import' => 'Importazione massiva',
        'wn_plus' => 'Portale WN+',
        'public_consent' => 'Link pubblico consensi',
        'system' => 'Sistema',
    ],

    'activities' => [
        'consent_request_sent' => 'Richiesta di consenso inviata',
        'consent_request_completed' => 'Richiesta di consenso completata',
        'consent_granted' => 'Consenso concesso',
        'consent_denied' => 'Consenso negato',
        'consent_revoked' => 'Consenso revocato',
        'wn_plus_invitation_sent' => 'Invito WN+ inviato',
        'organization_import_run' => 'Importazione organizzazioni eseguita',
        'organization_import_rolled_back' => 'Importazione organizzazioni annullata',
    ],

    'access_events' => [
        'login' => ['label' => 'Accesso effettuato', 'variant' => 'success'],
        'login_failed' => ['label' => 'Accesso fallito', 'variant' => 'danger'],
        'logout' => ['label' => 'Uscita', 'variant' => 'muted'],
        'login_lockout' => ['label' => 'Account bloccato per troppi tentativi', 'variant' => 'warning'],
        'wn_plus_login' => ['label' => 'Accesso WN+', 'variant' => 'success'],
        'wn_plus_login_failed' => ['label' => 'Accesso WN+ fallito', 'variant' => 'danger'],
        'wn_plus_logout' => ['label' => 'Uscita WN+', 'variant' => 'muted'],
    ],
];
