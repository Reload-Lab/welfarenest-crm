# CRM Data Model — Versione 0.6 Consolidata

## Perimetro
Questa versione consolidata integra il modello 0.5 con gli aggiornamenti 0.6.

Include:
- entità core
- classificazioni
- addresses polimorfica
- modello contatti con types/channels/usages
- notes
- consents con versioning
- custom fields
- logging

Fuori perimetro:
- WN+ / area riservata / login portale
- import staging layer
- SQL definitivo
- migration Laravel
- traduzioni UI complete

---

## 1. ENTITÀ CORE

### ORGANIZATIONS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo univoco dell’organizzazione |
| `name` | string |  | Nome breve/commerciale/usuale |
| `legal_name` | string |  | Denominazione legale ufficiale |
| `organization_type_id` | integer | FK | Tipo organizzazione |
| `vat_number` | string |  | Partita IVA del soggetto amministrativo |
| `tax_code` | string |  | Codice fiscale del soggetto amministrativo |
| `sdi_code` | string |  | Codice destinatario fatturazione elettronica |
| `is_split_payment` | boolean |  | Indica se il soggetto è soggetto a split payment |
| `is_active` | boolean |  | Indica se l’anagrafica è attiva |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

### PEOPLE

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo persona |
| `first_name` | string |  | Nome della persona |
| `last_name` | string |  | Cognome della persona |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

### PERSON_ORGANIZATION_RELATIONS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo relazione |
| `person_id` | integer | FK | Persona coinvolta nella relazione |
| `organization_id` | integer | FK | Organizzazione collegata |
| `qualification_id` | integer | FK nullable | Qualifica principale della persona nella relazione |
| `department_id` | integer | FK nullable | Area o dipartimento associato alla relazione |
| `start_date` | date |  | Data inizio relazione |
| `end_date` | date |  | Data fine relazione |
| `is_active` | boolean |  | Indica se la relazione è attiva |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

### LEADS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo lead |
| `first_name` | string |  | Nome lead |
| `last_name` | string |  | Cognome lead |
| `organization_name` | string |  | Nome organizzazione non ancora strutturata |
| `lead_status_id` | integer | FK | Stato attuale del lead |
| `lead_source_id` | integer | FK nullable | Provenienza del lead |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

---

## 2. TABELLE DI CLASSIFICAZIONE

### ORGANIZATION_TYPES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo tipo organizzazione |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome del tipo organizzazione |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il tipo è utilizzabile |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### ORGANIZATION_ROLES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo ruolo organizzazione |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome ruolo |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il ruolo è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### ORGANIZATION_ROLE_ASSIGNMENTS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo assegnazione ruolo |
| `organization_id` | integer | FK | Organizzazione a cui è assegnato il ruolo |
| `organization_role_id` | integer | FK | Ruolo assegnato |
| `created_at` | datetime |  | Data creazione assegnazione |
| `updated_at` | datetime |  | Data ultima modifica |

### QUALIFICATIONS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo qualifica |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome qualifica |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se la qualifica è attiva |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### DEPARTMENTS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo dipartimento |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome area/dipartimento |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il dipartimento è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### ADDRESS_TYPES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo tipo indirizzo |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome tipo indirizzo |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il tipo è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### CONTACT_TYPES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo tipo recapito |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome tipo recapito |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il tipo è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### CONTACT_CHANNELS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo canale specifico |
| `contact_type_id` | integer | FK | Tipo recapito padre |
| `code` | string |  | Codice tecnico stabile |
| `name` | string |  | Nome canale specifico |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il canale è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### CONTACT_USAGES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo uso del recapito |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome uso del recapito |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se l’uso è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### LEAD_STATUSES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo stato lead |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome stato lead |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se lo stato è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### LEAD_SOURCES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo fonte lead |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome fonte lead |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se la fonte è attiva |
| `sort_order` | integer |  | Ordinamento visualizzazione |

### CONSENT_TYPES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo tipo consenso |
| `code` | string | Unique | Codice tecnico stabile |
| `name` | string |  | Nome consenso |
| `description` | string | nullable | Descrizione breve |
| `is_active` | boolean |  | Indica se il tipo consenso è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |

---

## 3. ADDRESSES E CONTACT MODEL

### ADDRESSES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo indirizzo |
| `owner_type` | string | Polimorfico | Tipo entità proprietaria dell’indirizzo |
| `owner_id` | integer | Polimorfico | ID entità proprietaria |
| `address_type_id` | integer | FK | Tipo indirizzo |
| `label` | string |  | Etichetta libera dell’indirizzo |
| `street` | string |  | Via, piazza o indirizzo |
| `street_number` | string |  | Numero civico |
| `postal_code` | string |  | CAP |
| `city` | string |  | Comune |
| `province` | string |  | Provincia |
| `region` | string |  | Regione |
| `country` | string |  | Stato o paese |
| `is_primary` | boolean |  | Indica se è l’indirizzo principale |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

Valori attesi per `owner_type`:
- `organization`
- `person`
- `person_organization_relation`

### CONTACT_POINTS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo recapito |
| `owner_type` | string | Polimorfico | Tipo entità proprietaria del recapito |
| `owner_id` | integer | Polimorfico | ID entità proprietaria |
| `contact_type_id` | integer | FK | Tipo recapito |
| `contact_channel_id` | integer | FK nullable | Canale specifico del recapito |
| `contact_usage_id` | integer | FK nullable | Uso/funzione del recapito |
| `value` | string |  | Valore del recapito |
| `label` | string |  | Etichetta descrittiva del recapito |
| `is_primary` | boolean |  | Indica se è il recapito principale |
| `is_active` | boolean |  | Indica se il recapito è attivo |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

Valori attesi per `owner_type`:
- `organization`
- `person`
- `person_organization_relation`
- `lead`

---

## 4. NOTES, CONSENTS, LOGS

### NOTES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo nota |
| `owner_type` | string | Polimorfico | Tipo entità a cui è associata la nota |
| `owner_id` | integer | Polimorfico | ID entità a cui è associata la nota |
| `author_user_id` | integer | FK | Utente autore della nota |
| `content` | text |  | Contenuto della nota |
| `note_type` | string | nullable | Tipo nota |
| `is_pinned` | boolean |  | Indica se la nota è evidenziata |
| `created_at` | datetime |  | Data creazione nota |

### CONSENT_VERSIONS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo versione consenso |
| `consent_type_id` | integer | FK | Tipo consenso a cui appartiene la versione |
| `version_code` | string |  | Codice o numero versione |
| `title` | string |  | Titolo della versione del consenso |
| `content_text` | text | nullable | Testo completo del consenso |
| `content_file_path` | string | nullable | Percorso file del documento di consenso |
| `is_active` | boolean |  | Indica se la versione è attiva |
| `published_at` | datetime | nullable | Data pubblicazione/entrata in vigore |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

### CONSENTS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo consenso registrato |
| `consent_type_id` | integer | FK | Tipo consenso |
| `consent_version_id` | integer | FK nullable | Versione specifica del consenso |
| `owner_type` | string | Polimorfico | Tipo entità a cui si riferisce il consenso |
| `owner_id` | integer | Polimorfico | ID entità a cui si riferisce il consenso |
| `status` | string |  | Stato del consenso |
| `granted_at` | datetime | nullable | Data concessione consenso |
| `revoked_at` | datetime | nullable | Data revoca consenso |
| `source` | string | nullable | Origine o modalità di acquisizione |
| `created_at` | datetime |  | Data registrazione consenso |
| `updated_at` | datetime |  | Data ultima modifica |

### AUDIT_LOGS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo audit log |
| `user_id` | integer | FK nullable | Utente che ha generato l’evento |
| `event_type` | string |  | Tipo evento |
| `auditable_type` | string | Polimorfico | Tipo entità modificata |
| `auditable_id` | integer | Polimorfico | ID entità modificata |
| `old_values_json` | json | nullable | Valori precedenti |
| `new_values_json` | json | nullable | Valori nuovi |
| `created_at` | datetime |  | Data evento |

### ACTIVITY_LOGS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo activity log |
| `user_id` | integer | FK nullable | Utente che ha eseguito l’azione |
| `activity_type` | string |  | Tipo attività |
| `subject_type` | string | Polimorfico nullable | Tipo entità coinvolta |
| `subject_id` | integer | Polimorfico nullable | ID entità coinvolta |
| `module` | string |  | Modulo CRM interessato |
| `created_at` | datetime |  | Data evento |

### ACCESS_LOGS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo access log |
| `user_id` | integer | FK nullable | Utente associato all’evento |
| `event_type` | string |  | Tipo evento accesso |
| `ip_address` | string | nullable | Indirizzo IP |
| `user_agent` | string | nullable | Browser o device |
| `created_at` | datetime |  | Data evento |

---

## 5. CUSTOM FIELDS

### CUSTOM_FIELDS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo campo personalizzato |
| `name` | string |  | Nome del campo |
| `slug` | string | Unique | Chiave tecnica stabile del campo |
| `entity_type` | string |  | Entità a cui si applica il campo |
| `field_type` | string |  | Tipo campo |
| `organization_type_id` | integer | FK nullable | Limita il campo a uno specifico tipo organizzazione |
| `is_required` | boolean |  | Indica se il campo è obbligatorio |
| `is_active` | boolean |  | Indica se il campo è attivo |
| `sort_order` | integer |  | Ordinamento visualizzazione |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

Valori attesi per `entity_type`:
- `organization`
- `person`
- `person_organization_relation`
- `lead`

Valori attesi iniziali per `field_type`:
- `text`
- `textarea`
- `number`
- `date`
- `boolean`
- `select`
- `multiselect`

### CUSTOM_FIELD_OPTIONS

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo opzione campo |
| `custom_field_id` | integer | FK | Campo personalizzato di appartenenza |
| `value` | string |  | Valore tecnico dell’opzione |
| `label` | string |  | Etichetta visualizzata |
| `sort_order` | integer |  | Ordinamento visualizzazione |
| `is_active` | boolean |  | Indica se l’opzione è attiva |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

### CUSTOM_FIELD_VALUES

| Campo | Tipo | Chiave | Descrizione |
|------|------|--------|-------------|
| `id` | integer | PK | Identificativo valore campo |
| `custom_field_id` | integer | FK | Campo personalizzato di riferimento |
| `owner_type` | string | Polimorfico | Tipo entità proprietaria del valore |
| `owner_id` | integer | Polimorfico | ID entità proprietaria |
| `value_text` | string | nullable | Valore testuale |
| `value_number` | decimal/number | nullable | Valore numerico |
| `value_date` | date | nullable | Valore data |
| `value_boolean` | boolean | nullable | Valore booleano |
| `value_json` | json | nullable | Valore complesso, es. multiselect |
| `created_at` | datetime |  | Data creazione record |
| `updated_at` | datetime |  | Data ultima modifica |

---

## 6. VALORI INIZIALI CONSIGLIATI

### ORGANIZATION_ROLES
| Code | Nome |
|---|---|
| `client` | Cliente |
| `supplier` | Fornitore |
| `internal` | Interna |
| `partner` | Partner |

### CONTACT_TYPES
| Code | Nome |
|---|---|
| `email` | Email |
| `pec` | PEC |
| `phone` | Telefono |
| `mobile` | Cellulare |
| `website` | Sito web |
| `social` | Social |

### ADDRESS_TYPES
| Code | Nome |
|---|---|
| `legal` | Sede legale |
| `operational` | Sede operativa |
| `administrative` | Sede amministrativa |
| `residence` | Residenza |
| `domicile` | Domicilio |
| `work_location` | Sede di lavoro |
| `shipping` | Recapito spedizioni |
| `other` | Altro |

### LEAD_STATUSES
| Code | Nome |
|---|---|
| `new` | Nuovo |
| `working` | In lavorazione |
| `qualified` | Qualificato |
| `converted` | Convertito |
| `lost` | Perso |

### CONSENT_TYPES
| Code | Nome |
|---|---|
| `privacy_base` | Privacy Policy Base |
| `marketing` | Comunicazioni Marketing |

### CONTACT_CHANNELS (esempi iniziali)
Per `social`:
- `linkedin`
- `facebook`
- `instagram`

### CONTACT_USAGES
| Code | Nome |
|---|---|
| `personal` | Personale |
| `work` | Lavoro |
| `administrative` | Amministrativa |
| `office` | Ufficio |
| `direct` | Diretto |
| `support` | Supporto |

---

## 7. DECISIONI TRASVERSALI CONFERMATE

- database e codice in inglese
- interfaccia utente in italiano
- `organizations` rappresenta i soggetti amministrativi
- `people` rappresenta gli individui
- clienti e fornitori restano sul lato `organizations`
- tutti i recapiti stanno in `contact_points`
- gli indirizzi sono gestiti tramite `addresses` polimorfica
- niente campi `notes` sparsi nelle tabelle core
- WN+ fuori perimetro attuale
- import staging layer fuori perimetro attuale
