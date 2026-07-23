# CRM Data Model — v0.8 (stato realmente implementato)

> Fonte primaria: migration, model Eloquent e seeder presenti nel repository al 2026-07-23.
> Questo documento sostituisce v0.6/v0.7 come riferimento tecnico corrente; i documenti precedenti sono stati spostati in `docs/archive/` come materiale storico/pianificazione. In caso di conflitto tra pianificazione e codice, questo file riporta il codice.
>
> Per ogni tabella: **Stato** = 🟢 Completo (migration + model + controller/route + UI), 🟡 Parziale (manca una di queste parti, o copre solo un sottoinsieme degli owner previsti), 🔴 Solo pianificato (solo migration, nessun model/controller/uso applicativo).

---

## 1. Organizations

**Tabella**: `organizations` · **Modello**: `App\Models\Organization` · **Stato**: 🟢 Completo

Campi reali:
- `id`
- `name` — string, nullable
- `legal_name` — string, nullable
- `organization_type_id` — unsignedBigInteger, **non nullable**, FK → `organization_types.id` (`restrictOnDelete`)
- `vat_number` — string, nullable
- `tax_code` — string, nullable
- `sdi_code` — string, nullable
- `is_split_payment` — boolean, default `false`
- `is_active` — boolean, default `true`
- `avatar_path` — string, nullable (aggiunto in `2026_04_20_090658`)
- `created_at`, `updated_at`

Indici: `organization_type_id`, `is_active`.

`$fillable`: `name, legal_name, organization_type_id, vat_number, tax_code, sdi_code, is_split_payment, is_active`. `$casts`: `is_split_payment`, `is_active` → boolean. Accessor `getDisplayNameAttribute()` (`name ?: legal_name ?: 'Organizzazione'`), `getAvatarUrlAttribute()`.

Relazioni reali:
- `organizationType()` — belongsTo `OrganizationType`
- `organizationRoles()` — belongsToMany `OrganizationRole` tramite `organization_role_assignments`
- `personOrganizationRelations()` — hasMany `PersonOrganizationRelation`
- `personRelations()` — hasMany `PersonOrganizationRelation` (duplicato letterale della relazione precedente, stesso target, nessuna condizione differente — probabile residuo di refactor)
- `contactPoints()` — hasMany `ContactPoint` filtrata su `owner_type = 'organization'`
- `addresses()` — hasMany `Address` filtrata su `owner_type = 'organization'`, con eager-load di `addressType` e ordinamento `is_primary desc, address_type_id, city`
- `notes()` — hasMany `Note` filtrata su `owner_type = 'organization'`
- `wnPlusAccounts()` — hasMany `WnPlusAccount`

CRUD: `Route::resource('organizations', ...)` completo (index/create/store/show/edit/update/destroy), più `organizations/search`, e le viste `/clients` e `/suppliers` (index+show, stesso controller) per i ruoli client/supplier.

## 2. Organization types

**Tabella**: `organization_types` · **Modello**: `App\Models\OrganizationType` · **Stato**: 🟡 Parziale (tabella e uso completi, ma `$fillable` incompleto — vedi nota)

Campi reali: `id`, `code` (string, unique), `name`, `description` (string, nullable), `is_active` (default true), `sort_order` (default 0), timestamps. Indice: `is_active`.

`$fillable`: **solo `name`** — nonostante `code`, `description`, `is_active`, `sort_order` siano colonne reali della tabella, non sono assegnabili in massa (unico modello del gruppo "tabelle di classificazione" con questa limitazione: `Qualification`, `Department`, `OrganizationRole` hanno `$fillable` completo). Relazione: `organizations()` — hasMany `Organization`.

Seeder (`OrganizationTypeSeeder`): 25 tipi seedati (Altre aziende, Banca, Big pharma, Broker, Cassa di previdenza, Compagnia di assicurazione, Ente bilaterale, Erogatore sanitario, ETS, Fondo pensione, Fondo sanitario, Istituzioni, SGR, SMS, Società di consulenza [+ comunicazione, + IT], Studio attuariale, Studio commercialista, Studio consulenza del lavoro, Studio legale, Studi professionali, TPA, Università/Centro di ricerca, Utility), con `code` derivato automaticamente dal nome.

Nessuna route/CRUD web per questa tabella: gestita solo via seeder.

## 3. Organization roles

**Tabelle**: `organization_roles`, `organization_role_assignments` · **Modello**: `App\Models\OrganizationRole` · **Stato**: 🟡 Parziale (nessuna route/CRUD dedicata, solo assegnazione tramite relazione)

`organization_roles`: `id`, `code` (unique), `name`, `description` (nullable), `is_active`, `sort_order`, timestamps. Indice `is_active`.

`organization_role_assignments`: `id`, `organization_id` (FK → `organizations`, `cascadeOnDelete`), `organization_role_id` (FK → `organization_roles`, `restrictOnDelete`), timestamps. Vincolo unico su `(organization_id, organization_role_id)`.

`$fillable` di `OrganizationRole`: `code, name, description, is_active, sort_order`. Relazione: `organizations()` — belongsToMany `Organization`.

Seeder (`OrganizationRoleSeeder`): solo **3** ruoli — `client` (Cliente), `internal` (Interno), `supplier` (Fornitore). Il ruolo `partner` menzionato nella documentazione pianificata **non è seedato**.

Nessuna route web dedicata a `organization-roles` in `routes/web.php`.

## 4. People

**Tabella**: `people` · **Modello**: `App\Models\Person` · **Stato**: 🟢 Completo (anagrafica base)

Campi reali: `id`, `first_name` (string, non nullable, aggiunta il giorno dopo la creazione tabella — vedi nota migration), `last_name` (string, non nullable), `avatar_path` (string, nullable), `created_at`, `updated_at`.

Nessuna colonna `is_active`, `email`, o dato fiscale — coerente con la regola "Person non contiene mai dati amministrativi/fiscali".

`$fillable`: `first_name, last_name`. Nessun `$casts`. Traits: `HasFactory`, `HasConsents`. Accessor: `getFullNameAttribute()`, `getDisplayNameAttribute()`, `getAvatarUrlAttribute()`.

Relazioni reali:
- `organizationRelations()` — hasMany `PersonOrganizationRelation`
- `contactPoints()` — hasMany `ContactPoint` filtrata su `owner_type = 'person'`
- `wnPlusAccounts()` — hasMany `WnPlusAccount`

**Non esiste** una relazione `notes()` sul modello `Person` (vedi §8 — discrepanza).

CRUD: `Route::resource('people', ...)` completo, più `people/search`.

## 5. Person–organization relations

**Tabella**: `person_organization_relations` · **Modello**: `App\Models\PersonOrganizationRelation` · **Stato**: 🟢 Completo

Campi reali:
- `id`
- `person_id` — FK → `people.id`, **non nullable**, `cascadeOnDelete`
- `organization_id` — FK → `organizations.id`, **non nullable**, `cascadeOnDelete`
- `qualification_id` — FK → `qualifications.id`, nullable, `nullOnDelete`
- `department_id` — FK → `departments.id`, nullable, `nullOnDelete`
- `is_primary` — boolean, default `false`
- `is_active` — boolean, default `true`
- `start_date` — date, nullable (aggiunta in `2026_04_08_140000`)
- `end_date` — date, nullable (aggiunta in `2026_04_08_140000`)
- `created_at`, `updated_at`

Indici: `person_id`, `organization_id`, `qualification_id`, `department_id`, `is_primary`, `is_active`, `start_date`, `end_date`.

`$fillable`: `person_id, organization_id, qualification_id, department_id, start_date, end_date, is_primary, is_active`. `$casts`: `start_date`, `end_date` → date; `is_primary`, `is_active` → boolean.

Relazioni reali: `person()` belongsTo, `organization()` belongsTo, `qualification()` belongsTo, `department()` belongsTo, `contactPoints()` hasMany `ContactPoint` filtrata su `owner_type = 'person_organization_relation'`.

**Nota**: esiste anche un campo `is_primary` non documentato nei modelli v0.6/v0.7 (relazione "primaria" tra persona e organizzazione), oltre a `is_active`. Nessuna relazione `addresses()` definita — gli indirizzi polimorfici risultano collegati solo a Organization nel codice reale (vedi §7).

CRUD: nessuna resource route dedicata; store/update/destroy esposte sia da `people/{person}/relations/...` sia da `organizations/{organization}/relations/...`, gestite in modale sulle pagine di dettaglio.

## 6. Qualifications / Departments

**Tabelle**: `qualifications`, `departments` · **Modelli**: `Qualification`, `Department` · **Stato**: 🟡 Parziale (solo seeding, nessuna route/CRUD)

Schema identico per entrambe: `id`, `code` (unique), `name`, `description` (nullable), `is_active` (default true), `sort_order` (default 0), timestamps, indice `is_active`.

`$fillable` completo su entrambi i modelli: `code, name, description, is_active, sort_order`. Relazione su entrambi: `personOrganizationRelations()` — hasMany.

Seeder `QualificationSeeder` (12 voci): consulente, consulente attuariale, consulente comunicazione, consulente del lavoro, consulente IT, consulente legale, consulente medico-sanitario, direttore, docente, organi sociali, presidente, responsabile Ufficio/Area.

Seeder `DepartmentSeeder` (7 voci): area commerciale, area comunicazione, area formazione, area liquidazioni, area operativa, direzione, presidenza.

Nessuna route web per gestione CRUD di queste tabelle.

## 7. Contact points

**Tabella**: `contact_points` · **Modello**: `App\Models\ContactPoint` · **Stato**: 🟢 Completo (il modulo più maturo)

Campi reali: `id`, `owner_type` (string), `owner_id` (unsignedBigInteger), `contact_type_id` (FK → `contact_types`, `restrictOnDelete`), `contact_usage_id` (FK → `contact_usages`, nullable, `nullOnDelete`), `value` (string), `label` (string, nullable), `is_primary` (default false), `is_active` (default true), timestamps.

Indici: composito `(owner_type, owner_id)`, più `contact_type_id`, `contact_usage_id`, `is_primary`, `is_active`.

`$fillable`: `owner_type, owner_id, contact_type_id, contact_usage_id, value, label, is_primary, is_active`. `$casts`: `is_primary`, `is_active` → boolean. Traits: `HasFactory`, `HasConsents` (sì — anche `ContactPoint` può avere consensi propri, oltre a `Person`).

Relazioni reali: `contactType()` belongsTo, `contactUsage()` belongsTo. **Nessun metodo `owner()`** definito sul modello (owner_type è una stringa semplice, non risolta via morph map su questo modello).

Owner realmente supportati nel controller: `organization`, `person`, `person_organization_relation` (nessun contact point per `lead`, poiché Lead non esiste — vedi §11).

Logica applicativa reale (in `ContactPointController`): un solo `is_primary` per `(owner_type, owner_id, contact_type_id)`; se l'owner è una persona e il tipo contatto ha categoria `email`, viene automaticamente attivato `ConsentRequestService::createForContactPoint()`.

CRUD: create nidificata per organization/person/relation, update/destroy standalone su `contact-points/{contactPoint}`. Nessuna route index/show dedicata (rese inline nelle pagine owner). Partial riutilizzabile: `resources/views/contact-points/_list.blade.php`.

## 8. Contact types / Contact usages

**Tabelle**: `contact_types`, `contact_usages` · **Modelli**: `ContactType`, `ContactUsage` · **Stato**: 🟢 Completo

`contact_types`: `id`, `code` (unique), `name`, `category` (string, non nullable), `description` (nullable), `sort_order`, `is_active`, timestamps. Indici: `category`, `is_active`.

Seeder (`ContactTypeSeeder`, 8 voci): `email`/email, `pec`/email, `linkedin`/social, `facebook`/social, `instagram`/social, `phone`/phone, `mobile`/phone, `website`/web. Le categorie (`email`, `social`, `phone`, `web`) sono referenziate letteralmente in `ContactPointController::validateValueByCategory`.

`contact_usages`: stesso schema classificatorio, **senza** colonna `category`. Seeder (`ContactUsageSeeder`, 7 voci): `administrative`, `commercial`, `direct`, `office`, `personal`, `support`, `work` (il codice `commercial` non era menzionato nel riepilogo, `office`/`personal`/`work`/`direct`/`support` sì).

Confermato: `contact_channels` e `contact_channel_id` non esistono nello schema reale — la semplificazione descritta in v0.7 è effettiva.

## 9. Addresses

**Tabella**: `addresses` · **Modello**: `App\Models\Address` · **Stato**: 🟡 Parziale (schema completo, ma UI/CRUD solo per Organization)

Campi reali: `id`, `owner_type`, `owner_id`, `address_type_id` (FK → `address_types`, `restrictOnDelete`), `label` (string, **nullable** — resa nullable da `2026_05_06_124445`, non nullable alla creazione), `street`, `street_number`, `postal_code`, `city`, `province`, `region`, `country` (tutte string non nullable), `is_primary` (default false), timestamps.

Indici: composito `(owner_type, owner_id)`, `address_type_id`, `is_primary`. **Nessuna colonna `is_active`** (a differenza di `contact_points`).

`$fillable`: `owner_type, owner_id, address_type_id, label, street, street_number, postal_code, city, province, region, country, is_primary`. `$casts`: `is_primary` → boolean. Relazione: `addressType()` belongsTo. Nessun metodo `owner()`.

Owner realmente supportati: **solo `organization`**. `AddressController` espone esclusivamente `storeForOrganization` / `updateForOrganization` / `destroyForOrganization`, e le uniche route sono nidificate sotto `organizations/{organization}/addresses`. Non esiste alcuna route/metodo per indirizzi di persona o di relazione, nonostante la tabella e il modello siano generici (vedi §11 — discrepanza rispetto alla pianificazione che prevedeva owner `organization | person | person_organization_relation`).

**Address types** (`address_types`, modello `AddressType`): schema classificatorio standard, nessuna `category`. Seeder (8 voci): `administrative`, `domicile`, `legal`, `operational`, `other`, `residence`, `shipping`, `work_location`.

## 10. Notes

**Tabella**: `notes` · **Modello**: `App\Models\Note` · **Stato**: 🟡 Parziale (implementato ma solo per Organization)

Campi reali: `id`, `owner_type`, `owner_id`, `author_user_id` (FK → `users`, `restrictOnDelete`), `content` (text), `note_type` (string, nullable), `is_pinned` (boolean, default false), `status` (string, default `'active'`, aggiunta in `2026_05_19_142913`), `created_at` (solo `created_at`, nessun `updated_at` — `const UPDATED_AT = null` nel modello).

Indici: composito `(owner_type, owner_id)`, `author_user_id`, `note_type`, `is_pinned`, `status`.

`$fillable`: `owner_type, owner_id, author_user_id, content, note_type, status, is_pinned`. `$casts`: `is_pinned` → boolean, `created_at` → datetime. Costanti: `STATUS_ACTIVE = 'active'`, `STATUS_ARCHIVED = 'archived'`. Scope: `scopeActive()`, `scopeArchived()`. Relazione: `author()` belongsTo `User`.

**Non usa Eloquent SoftDeletes** (nessuna colonna `deleted_at`): archiviazione/ripristino sono implementati tramite la colonna `status`, non tramite soft delete. `destroy()` esegue una **hard delete** reale.

`NoteController::store()` accetta esclusivamente `Organization $organization` e imposta `owner_type = 'organization'` in modo hardcoded: **non esiste alcun endpoint per creare note su Person o su PersonOrganizationRelation**, benché lo schema sia polimorfico generico (vedi §11 — discrepanza).

CRUD: `organizations/{organization}/notes` (store), `notes/{note}/archive`, `notes/{note}/restore`, `notes/{note}/toggle-pinned`, `notes/{note}` (destroy).

## 11. Consents

**Tabelle**: `consent_types`, `consent_versions`, `consents` · **Modelli**: `ConsentType`, `ConsentVersion`, `Consent` · **Stato**: 🟢 Completo (schema e servizio); 🟡 UI parziale (nessun centro consensi trasversale)

`consent_types`: `id`, `code` (unique), `name`, `category` (string, default `'consent'`, aggiunta in `2026_05_28_135640`), `description` (**text** nullable, ampliata da string a text nella stessa migration), `sort_order`, `is_active`, timestamps. Indice `is_active`.

`consent_versions`: `id`, `consent_type_id` (FK, `restrictOnDelete`), `version_code`, `title`, `content_text` (text, nullable), `content_file_path` (nullable), `is_active`, `published_at` (nullable), timestamps. Vincolo unico `(consent_type_id, version_code)` aggiunto in `2026_05_28_135640`.

`consents`: `id`, `consent_type_id` (FK, `restrictOnDelete`), `consent_version_id` (FK, nullable, `nullOnDelete`), `owner_type`, `owner_id`, `status`, `granted_at` (nullable), `revoked_at` (nullable), `source` (nullable), più — aggiunti in `2026_05_28_135640` — `requested_at` (nullable), `denied_at` (nullable), `created_by_user_id` (FK → `users`, nullable, `nullOnDelete`), `notes` (text, nullable), `evidence_file_path` (nullable), timestamps. Indici: composito `(owner_type, owner_id)`, `consent_type_id`, `consent_version_id`, `status`, `granted_at`, `revoked_at`, `requested_at`, `denied_at`.

**`ConsentType` definisce le costanti**: `PRIVACY_NOTICE = 'privacy_notice'`, `PROMOTIONAL_EMAILS = 'promotional_emails'`, `IMAGE_DISCLOSURE = 'image_disclosure'`.

Seeder `ConsentTypeSeeder` (3 righe, `updateOrCreate` per `code`):

| code | name | category |
|---|---|---|
| `privacy_notice` | Informativa privacy | `privacy_notice` |
| `promotional_emails` | Comunicazioni promozionali | `consent` |
| `image_disclosure` | Utilizzo e divulgazione immagini | `consent` |

Seeder `ConsentVersionSeeder`: una versione per tipo, tutte `version_code = 'v1_2026'`.

**I codici `privacy_base` e `marketing` non esistono in alcun punto del codice reale** (né migration, né seeder, né model) — vedi §12, discrepanza segnalata dall'utente stesso e qui confermata.

`Consent` — `$fillable`: `owner_type, owner_id, consent_type_id, consent_version_id, status, requested_at, granted_at, revoked_at, denied_at, source, created_by_user_id, notes, evidence_file_path`. `$casts`: le quattro colonne data → datetime. Relazioni: `owner()` morphTo, `consentType()` belongsTo, `consentVersion()` belongsTo, `createdByUser()` belongsTo `User`.

`ConsentService` (`app/Services/ConsentService.php`) usa i literal di stato `'granted'`/`'denied'` (nessuna costante/enum di stato); API pubblica: `grant()`, `deny()`, `latest()`, `hasGranted()`. L'`owner_type` è generico (passato dal chiamante, nessun valore hardcoded nel service).

## 12. Consent requests

**Tabella**: `consent_requests` · **Modello**: `App\Models\ConsentRequest` · **Stato**: 🟡 Parziale (creazione/visualizzazione presenti, invio della decisione assente)

Campi reali: `id`, `token` (string, unique), `owner_type`, `owner_id`, `contact_point_id` (FK → `contact_points`, nullable, `nullOnDelete`), `created_by_user_id` (FK → `users`, nullable, `nullOnDelete`), `expires_at` (non nullable), `sent_at` (nullable), `completed_at` (nullable), `status` (default `'pending'`), `source` (default `'email_request'`), timestamps. Indici: composito `(owner_type, owner_id)`, `status`, `expires_at`.

`$fillable`: `token, owner_type, owner_id, contact_point_id, created_by_user_id, expires_at, sent_at, completed_at, status, source`. Relazioni: `owner()` morphTo, `contactPoint()` belongsTo, `createdByUser()` belongsTo `User`.

`ConsentRequestService` usa il literal `'pending'` come stato iniziale e `'email_request'` come `source`; nessun altro literal di stato (`sent`/`completed`/`expired`) compare nel service.

**`ConsentRequestController` espone un solo metodo pubblico: `show(string $token)`** (lookup per token + stato `pending`, 410 se scaduto, vista `consent-requests.show`). **Non esiste alcuna route/metodo POST per registrare la decisione del destinatario** (accettazione/rifiuto dei singoli consensi): il flusso "completato senza autenticazione" descritto concettualmente nella documentazione di progetto non è chiuso end-to-end nel codice attuale — coerente con quanto riportato in `riepilogo_progetto.md` §4.11/§5.7 (flusso avviato, non concluso).

Unica route: `GET /consent-requests/{token}` → `consent-requests.show`.

## 13. Leads

**Tabelle**: `leads`, `lead_statuses`, `lead_sources` · **Modello**: nessuno · **Stato**: 🔴 Solo pianificato

Le tre migration esistono e sono complete: `lead_statuses`/`lead_sources` con lo schema classificatorio standard (`id, code, name, description, is_active, sort_order, timestamps`); `leads` con `id`, `organization_id` (nullable, FK, `nullOnDelete`), `person_id` (nullable, FK, `nullOnDelete`), `lead_status_id` (FK, **non nullable**, `restrictOnDelete`), `lead_source_id` (nullable, FK, `nullOnDelete`), `name`, `description` (text, nullable), `estimated_value` (decimal 12,2, nullable), `expected_close_date` (date, nullable), `assigned_user_id` (nullable, FK → `users`, `nullOnDelete`), `is_active`, timestamps.

**Non esiste alcun model** (`Lead`, `LeadStatus`, `LeadSource`), **nessun controller**, **nessuna route**, nessun riferimento nei seeder. Le tabelle, se migrate, resterebbero vuote e inutilizzate da qualunque codice applicativo.

## 14. Custom fields

**Tabelle**: `custom_fields`, `custom_field_options`, `custom_field_values` · **Modello**: nessuno · **Stato**: 🔴 Solo pianificato

`custom_fields`: `id`, `name`, `slug` (unique), `entity_type`, `field_type`, `organization_type_id` (nullable, FK, `nullOnDelete`), `is_required` (default false), `is_active`, `sort_order`, timestamps.
`custom_field_options`: `id`, `custom_field_id` (FK, `cascadeOnDelete`), `value`, `label`, `sort_order`, `is_active`, timestamps.
`custom_field_values`: `id`, `custom_field_id` (FK, `cascadeOnDelete`), `owner_type`, `owner_id`, `value_text`, `value_number` (decimal 15,4), `value_date`, `value_boolean`, `value_json`, timestamps.

**Nessun model, controller, service o route** referenzia queste tabelle in alcun punto del codice.

## 15. Logging (audit / activity / access)

**Tabelle**: `audit_logs`, `activity_logs`, `access_logs` · **Modello**: nessuno · **Stato**: 🔴 Solo pianificato — tabelle completamente inerti

Schema di tutte e tre: `id`, `user_id` (nullable, FK → `users`, `nullOnDelete`), colonne specifiche (`event_type`/`auditable_type`+`auditable_id`+`old_values_json`+`new_values_json` per audit; `activity_type`/`subject_type`+`subject_id`+`properties_json` per activity; `event_type`/`ip_address`/`user_agent` per access), solo `created_at` (nessun `updated_at`).

Verificato che **non esiste alcun model, observer, listener o scrittura manuale** (`DB::table(...)->insert()`) verso queste tre tabelle in tutto il codice applicativo (non esistono nemmeno le cartelle `app/Observers` o `app/Listeners`). Se migrate, le tabelle rimarrebbero permanentemente vuote con il codice attuale.

## 16. WN Plus — Accounts

**Tabella**: `wn_plus_accounts` · **Modello**: `App\Models\WnPlusAccount` · **Stato**: 🟢 Completo

Campi reali: `id`, `uuid` (unique), `organization_id` (FK, **non nullable**, `cascadeOnDelete`), `person_id` (FK, nullable, `nullOnDelete`), `first_name`, `last_name`, `email` (unique), `password` (nullable), `wn_plus_role_id` (FK, `restrictOnDelete`), `wn_plus_level_id` (FK, `restrictOnDelete`), `status` (string, default `'invited'` — valori applicativi: `invited | active | suspended | disabled`, non un enum DB), `account_type` (string, default `'user'`, aggiunta in `2026_06_12_143436`), `max_users` (nullable), `invited_by_account_id` (self-FK nullable, `nullOnDelete`), `created_by_user_id` (FK, nullable, `nullOnDelete`), `email_verified_at` (nullable), `last_login_at` (nullable), timestamps.

Indici compositi: `(organization_id, wn_plus_role_id)`, `(organization_id, wn_plus_level_id)`, `(organization_id, status)`, `(organization_id, account_type)`.

`$fillable` completo su tutti i campi sopra (eccetto id/uuid gestiti a parte). `$hidden`: `password`. `$casts`: `email_verified_at`, `last_login_at` → datetime.

Relazioni reali: `organization()` belongsTo, `person()` belongsTo, `role()` belongsTo `WnPlusRole`, `level()` belongsTo `WnPlusLevel`, `invitations()` hasMany `WnPlusInvitation`, `invitedBy()` belongsTo (self), `invitedAccounts()` hasMany (self), `createdBy()` belongsTo `User`. Accessor: `getFullNameAttribute()`, `getManagedUsersCountAttribute()`, `getAvailableSlotsAttribute()`.

**Nota**: `status = 'invited'` di default, non `'pending'` come indicato nella documentazione pianificata (differenza terminologica, stesso significato funzionale).

## 17. WN Plus — Roles / Levels / Invitations / OIDC

**`wn_plus_roles`** (modello `WnPlusRole`): `id, code (unique), name, description (text, nullable), is_active, sort_order, timestamps`. Seeder: `manager` (Referente), `user` (Utente).

**`wn_plus_levels`** (modello `WnPlusLevel`): stesso schema. Seeder: `base` (Base), `premium` (Premium), `pro` (Pro).

**`wn_plus_invitations`** (modello `WnPlusInvitation`): `id`, `wn_plus_account_id` (FK, `cascadeOnDelete`), `token` (unique), `expires_at` (non nullable), `sent_at` (nullable), `accepted_at` (nullable), timestamps. `$fillable`: tutti i campi sopra. Relazione: `account()` belongsTo.

**`wn_plus_oidc_clients`** (modello `WnPlusOidcClient`): `id, name, client_id (unique), client_secret, redirect_uri (text), is_active, timestamps`. Nessuna relazione definita sul modello.

**`wn_plus_oidc_auth_codes`** (modello `WnPlusOidcAuthCode`): `id`, `wn_plus_oidc_client_id` (FK, `cascadeOnDelete`), `wn_plus_account_id` (FK, `cascadeOnDelete`), `code` (unique), `redirect_uri`, `scope` (nullable), `nonce` (nullable), `expires_at` (non nullable), `used_at` (nullable), timestamps. Relazioni: `client()`, `account()` (belongsTo, tipo di ritorno non dichiarato).

Controller reali: `WnPlusAccountController` (index/create/store/edit/show/update/createUser/storeUser/sendInvitation), `WnPlusAuthController` (showLogin/login/logout), `WnPlusInvitationController` (accept/complete), `WnPlusOidcController` (configuration/authorize/token/jwks/userinfo). Tutte le route WN+ e OIDC vivono in `routes/web.php` (non esiste un file route separato).

Stato: 🟢 Completo per account/ruoli/livelli/inviti/OIDC di base; **manca** — coerentemente con `riepilogo_progetto.md` §5.9/§5.10 — l'amministrazione avanzata (sospensione, filtri, storico login, enforcement di `max_users`) e il single logout OIDC.

## 18. Polimorfismo — stato reale del morph map

`App\Providers\AppServiceProvider::boot()` registra:
```php
Relation::morphMap([
    'person' => Person::class,
    'contact_point' => ContactPoint::class,
]);
```
Solo **due** owner type sono effettivamente mappati (`person`, `contact_point`), nonostante lo schema utilizzi `owner_type` come stringa controllata anche per `organization`, `person_organization_relation`, `lead`, `wn_plus_account`. Le relazioni `morphTo()` definite su `Consent::owner()` e `ConsentRequest::owner()` risolvono correttamente solo per i due tipi mappati; per gli altri valori di `owner_type` la risoluzione dipenderebbe dal comportamento di default di Eloquent (nessuna enforceMorphMap attiva). Questo è coerente con il punto aperto §8.1 di `riepilogo_progetto.md` ("morph map definitiva" ancora da completare) e non richiede una correzione applicativa in questo task, che è di sola documentazione.

---

## Riepilogo stato moduli

| Modulo | Stato |
|---|---|
| Organizations | 🟢 Completo |
| Organization types | 🟡 Parziale ($fillable incompleto, no CRUD web) |
| Organization roles | 🟡 Parziale (solo 3 ruoli seedati, no CRUD web) |
| People | 🟢 Completo |
| Person–organization relations | 🟢 Completo |
| Qualifications / Departments | 🟡 Parziale (no CRUD web) |
| Contact points | 🟢 Completo |
| Contact types / usages | 🟢 Completo |
| Addresses | 🟡 Parziale (solo owner Organization) |
| Notes | 🟡 Parziale (solo owner Organization) |
| Consents | 🟢 Completo (schema/servizio), 🟡 UI (no centro consensi) |
| Consent requests | 🟡 Parziale (manca l'invio della decisione) |
| Leads | 🔴 Solo pianificato |
| Custom fields | 🔴 Solo pianificato |
| Logging (audit/activity/access) | 🔴 Solo pianificato, tabelle inerti |
| WN Plus (accounts/roles/levels/invitations/OIDC) | 🟢 Completo (base), 🟡 amministrazione avanzata |
