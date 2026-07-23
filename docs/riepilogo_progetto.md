# Welfare Nest CRM — Riepilogo tecnico e funzionale completo
## 1. Contesto generale del progetto
Il progetto è un CRM interno sviluppato con Laravel 12 per la gestione del network Welfare Nest.

L’applicazione è nata da un primo CRUD generico denominato “Clienti”, successivamente rifattorizzato verso un modello CRM più strutturato, nel quale:

- le aziende, gli enti, i fondi e gli altri soggetti amministrativi sono rappresentati da organizations ;
- gli individui sono rappresentati da people ;
- il rapporto tra una persona e un’organizzazione è un’entità autonoma;
- email, telefoni, PEC, siti e social sono gestiti centralmente tramite contact_points ;
- gli indirizzi sono gestiti tramite addresses ;
- i consensi sono entità versionate e tracciabili;
- WN+ è stato sviluppato come modulo separato ma integrato con le anagrafiche CRM.

Il primo stato documentato del progetto prevedeva Laravel 12, PHP 8.4, Bootstrap, SQLite locale, Fortify, Git e GitHub, con un CRUD Client già funzionante. Quel modulo è stato poi superato dal modello Organization / Person.

Lo stack effettivamente utilizzato nelle fasi più recenti è:

- Laravel 12;
- PHP 8.3 sul server di sviluppo interno;
- PHP 8.4 nell’ambiente locale;
- Blade;
- Bootstrap 5;
- Vite;
- MariaDB/MySQL negli ambienti server;
- autenticazione Laravel;
- Git per il versionamento;
- SMTP autenticato tramite Gmail Relay per le email;
- integrazione OpenID Connect con il sito WordPress WN+.

## 2. Modello dati
### 2.1 Organizations

#### Tabella

organizations

#### Modello

`App\Models\Organization`

#### Scopo

Rappresenta qualsiasi soggetto amministrativo o organizzato:

- azienda;
- fondo pensione;
- fondo sanitario;
- banca;
- SGR;
- associazione;
- ente;
- fornitore;
- partner;
- società interna;
- professionista o altro soggetto amministrativo.

Una Organization non rappresenta necessariamente un cliente: il ruolo commerciale o operativo è gestito separatamente.

#### Campi principali definiti

- id
- name
- legal_name
- organization_type_id
- vat_number
- tax_code
- sdi_code
- is_split_payment
- is_active
- created_at
- updated_at

Nei documenti iniziali il principio era che almeno uno tra name e legal_name dovesse essere valorizzato.

#### Relazioni principali

- belongsTo OrganizationType
- belongsToMany OrganizationRole , tramite organization_role_assignments
- hasMany PersonOrganizationRelation
- relazioni polimorfiche verso:
- contact_points
- addresses
- notes
- hasMany WnPlusAccount
- può essere utilizzata come soggetto organizzativo nei flussi WN+.

#### Regole di business

- Una organizzazione può avere più ruoli contemporaneamente.
- “Cliente”, “fornitore”, “partner” e simili non sono tipi di organizzazione.
- organization_type_id descrive che cosa è l’organizzazione.
- organization_roles descrive che rapporto ha con Welfare Nest.
- Partita IVA e codice fiscale non sono obbligatori per tutte le organizzazioni.
- I recapiti non devono essere salvati direttamente in organizations .
- Gli indirizzi non devono essere salvati direttamente in organizations .
- La disattivazione logica è preferibile alla cancellazione distruttiva nei casi in cui esistano relazioni collegate.

### 2.2 Organization types

#### Tabella

`organization_types`

#### Modello

`OrganizationType`

#### Campi standard

- id
- code , univoco
- name
- description , nullable
- is_active
- sort_order
- timestamp

#### Scopo

Classifica la natura dell’organizzazione.

Esempi definiti o ipotizzati:

- pension_fund
- health_fund
- bank
- sgr
- insurance_company
- consulting_company
- it_company
- law_firm
- university
- institution
- other

I codici tecnici devono essere stabili e indipendenti dalla traduzione mostrata nella UI.

### 2.3 Organization roles

#### Tabelle

- organization_roles
- organization_role_assignments

#### Modelli

- OrganizationRole
- modello pivot o relazione many-to-many

Campi di organization_roles

- id
- code
- name
- description
- is_active
- sort_order
- timestamp

Campi di organization_role_assignments

- id
- organization_id
- organization_role_id
- timestamp

#### Ruoli iniziali definiti

- client
- supplier
- internal
- partner

#### Decisione importante

Un’organizzazione può essere, contemporaneamente, cliente e partner oppure cliente e fornitore. Per questo i ruoli non sono stati rappresentati con singoli booleani dentro organizations.

### 2.4 People

#### Tabella

people

#### Modello

`App\Models\Person`

#### Scopo

Rappresenta esclusivamente un individuo.

#### Campi principali

- id
- first_name
- last_name
- created_at
- updated_at

Nel modello di base non sono stati inseriti direttamente:

- email;
- telefono;
- organizzazione;
- qualifica;
- dipartimento;
- dati amministrativi dell’organizzazione.

Queste informazioni appartengono ad altre entità.

#### Relazioni principali

- hasMany PersonOrganizationRelation
- relazioni polimorfiche verso:
- contact_points
- addresses
- notes
- consents
- può essere associata a uno o più account WN+.

#### Regole di business

- Una persona può esistere senza essere associata a un’organizzazione.
- Una persona può avere più rapporti con la stessa organizzazione.
- Nome e cognome costituiscono il nucleo minimo dell’anagrafica.
- I recapiti personali appartengono direttamente alla persona.
- I recapiti lavorativi specifici appartengono preferibilmente alla relazione persona-organizzazione.

Il glossario distingue esplicitamente Organization, soggetto amministrativo, e Person, individuo.

### 2.5 Person–organization relations

#### Tabella

`person_organization_relations`

#### Modello

Il nome utilizzato è stato PersonOrganizationRelation, o una denominazione equivalente coerente con la tabella.

#### Scopo

Rappresenta il legame tra una persona e una organizzazione.

Non è considerata una semplice pivot, perché contiene dati propri.

#### Campi principali

- id
- person_id
- organization_id
- qualification_id , nullable
- department_id , nullable
- start_date , nullable o valorizzabile
- end_date , nullable
- is_active
- created_at
- updated_at

#### Relazioni

- belongsTo Person
- belongsTo Organization
- belongsTo Qualification
- belongsTo Department
- relazioni polimorfiche verso:
- contact_points
- addresses
- notes
- eventualmente consensi specifici del rapporto

#### Regole di business

- Una persona può avere più relazioni con la stessa organizzazione.
- Questo consente:
- storico di incarichi;
- incarichi contemporanei;
- qualifiche differenti;
- passaggi tra dipartimenti.
- end_date non può precedere start_date .

- is_active è separato dalle date, per consentire gestione manuale dello stato.
- La qualifica appartiene alla relazione, non alla persona.
- Il dipartimento appartiene alla relazione, non alla persona.
- L’email aziendale può appartenere alla relazione, perché può cambiare in base all’organizzazione.

### 2.6 Qualifications

#### Tabella

qualifications

#### Modello

`Qualification`

#### Campi

- id
- code
- name
- description
- is_active
- sort_order
- timestamp

`Esempi`

- presidente;
- direttore;
- responsabile area;
- consulente;
- membro del consiglio di amministrazione;
- referente;
- consulente legale;
- consulente IT.

### 2.7 Departments

#### Tabella

departments

#### Modello

`Department`

#### Campi

- id
- code
- name
- description
- is_active
- sort_order
- timestamp

`Esempi`

- presidenza;
- direzione;
- area operativa;
- area commerciale;
- comunicazione;
- formazione;
- liquidazioni;
- amministrazione.

### 2.8 Contact points

#### Tabella

`contact_points`

#### Modello

`App\Models\ContactPoint`

#### Scopo

Gestisce in modo centralizzato tutti i recapiti:

- email;
- PEC;
- telefono;
- cellulare;
- sito web;
- LinkedIn;
- Facebook;
- Instagram;
- altri social o canali.

#### Campi principali

- id
- owner_type
- owner_id

- contact_type_id
- contact_usage_id , nullable
- value
- label , nullable
- is_primary
- is_active
- created_at
- updated_at

#### Owner ammessi

- organization
- person
- person_organization_relation
- lead

#### Evoluzione del modello

Nella versione 0.6 era prevista una tabella contact_channels con un campo contact_channel_id in contact_points.

Successivamente questa struttura è stata semplificata:

- contact_channels è stata rimossa;
- contact_channel_id è stato rimosso;
- contact_types è stata estesa con il campo category .

Per esempio:

| code | name | category |
|---|---|---|
| `email` | Email | `email` |
| `pec` | PEC | `email` |
| `phone` | Telefono | `phone` |
| `mobile` | Cellulare | `phone` |
| `linkedin` | LinkedIn | `social` |
| `website` | Sito web | `web` |
Questa è la struttura aggiornata della versione 0.7.

#### Regole di business

- Un recapito appartiene a un solo owner.
- Il valore deve essere validato in base alla categoria.
- Per email e pec deve essere applicata una validazione email.
- Per phone e mobile deve essere applicata una validazione sufficientemente permissiva per prefissi internazionali, spazi e separatori.
- È consigliato un solo recapito primario per:

- owner;
- tipo di contatto.
- contact_usage_id descrive l’utilizzo del recapito.
- I recapiti disattivati rimangono nello storico.
- L’ordinamento visivo concordato privilegia:
- email e PEC;
- telefoni;
- sito web;
- social.

#### Funzionalità UI collegate

Sono state introdotte CTA automatiche in base al tipo:

- email → collegamento mailto: ;
- telefono → collegamento tel: ;
- sito → apertura URL;
- social → apertura profilo;
- copia del valore negli appunti.

### 2.9 Contact types

#### Tabella

`contact_types`

#### Modello

`ContactType`

#### Campi aggiornati

- id
- code
- name
- category
- description
- is_active
- sort_order
- timestamp

#### Decisione architetturale

code identifica il tipo specifico; category permette comportamenti generali.

Esempi:

- email e pec hanno categoria email ;
- phone e mobile hanno categoria phone ;

- linkedin , facebook , instagram hanno categoria social ;
- website ha categoria web .

La UI e i componenti possono quindi scegliere icona, validazione e azione usando category, senza mantenere una tabella separata dei canali.

### 2.10 Contact usages

#### Tabella

`contact_usages`

#### Modello

`ContactUsage`

#### Campi

- id
- code
- name
- description
- is_active
- sort_order
- timestamp

#### Valori iniziali

- personal
- work
- administrative
- office
- direct
- support

### 2.11 Addresses

#### Tabella

addresses

#### Modello

`Address`

#### Campi principali

- id

- owner_type
- owner_id
- address_type_id
- label
- street
- street_number
- postal_code
- city
- province
- region
- country
- is_primary
- created_at
- updated_at

#### Owner ammessi

- organization
- person
- person_organization_relation

#### Tipi indirizzo

Tabella address_types, con esempi:

- legal
- operational
- administrative
- residence
- domicile
- work_location
- shipping
- other

#### Regole

- Un indirizzo appartiene a un solo owner.
- È consigliato un solo indirizzo primario per owner e tipo.
- La sede legale e la sede operativa non sono colonne di organizations , ma record distinti in addresses .

### 2.12 Notes

#### Tabella

notes

#### Modello

`App\Models\Note`

#### Campi iniziali

- id
- owner_type
- owner_id
- author_user_id
- content
- note_type , nullable
- is_pinned
- created_at

Nel modulo reale sono state aggiunte o utilizzate funzionalità compatibili con:

- archiviazione;
- ripristino;
- eliminazione;
- nota fissata in evidenza.

È quindi probabile che la struttura effettiva comprenda anche soft delete, stato di archivio o campi equivalenti.

#### Owner

Le note sono state progettate come polimorfiche e possono essere associate alle principali entità CRM.

#### Regole

- Ogni entità può avere più note.
- Le note sono cronologiche.
- Ogni nota ha un autore.
- Una nota non rappresenta un task o un’attività pianificata.
- Non devono essere aggiunte colonne notes libere nelle tabelle core.

### 2.13 Consents

#### Tabelle

- consent_types
- consent_versions
- consents
- successivamente consent_requests

#### Modelli

- ConsentType
- ConsentVersion

- Consent
- ConsentRequest

`consent_types`

Campi principali:

- id
- code
- name
- description
- category
- is_active
- sort_order
- timestamp

Il campo category è stato aggiunto durante lo sviluppo per distinguere meglio le tipologie.

#### Tipi di consenso effettivamente predisposti

Sono stati creati o aggiornati seeder per almeno:

- privacy_notice
- promotional_emails
- image_disclosure

Categorie e significati:

- privacy_notice : presa visione o accettazione informativa privacy;
- promotional_emails : consenso alle comunicazioni promozionali;
- image_disclosure : consenso alla pubblicazione o utilizzo di immagini.

Nelle versioni preliminari erano usati i codici privacy_base e marketing; il modello implementato più recente usa codici più specifici.

`consent_versions`

Campi:

- id
- consent_type_id
- version_code
- title
- content_text , nullable
- content_file_path , nullable
- is_active
- published_at , nullable
- timestamp

Vincolo concordato:

- unicità della coppia consent_type_id + version_code .

consents

Campi di base:

- id
- consent_type_id
- consent_version_id , nullable
- owner_type
- owner_id
- status
- granted_at , nullable
- revoked_at , nullable
- source , nullable
- timestamp

Campi aggiunti nel corso dello sviluppo:

- requested_at
- denied_at
- created_by_user_id
- notes
- evidence_file_path

Sono stati inoltre aggiunti indici per rendere più efficienti le ricerche per owner, tipo e stato.

Owner dei consensi

Il modello iniziale prevedeva:

- person
- lead
- contact_point

Con WN+ è stato aggiunto:

- wn_plus_account

#### Stati

- pending
- granted
- denied
- revoked

#### Regole di business

- Il consenso è un’entità autonoma, non un booleano.
- Deve essere possibile identificare:

- il tipo di consenso;
- la versione;
- il soggetto;
- lo stato;
- la data;
- la fonte;
- l’eventuale prova documentale;
- l’operatore che lo ha registrato.
- consent_version_id , se valorizzato, deve appartenere allo stesso consent_type_id .
- Il consenso marketing può essere legato al singolo recapito, perché una persona potrebbe autorizzare l’utilizzo di una email ma non di un’altra.
- I consensi raccolti nel flusso WN+ usano source = wn_plus_onboarding .

Il modello 0.6 aveva già stabilito che i consensi dovessero essere entità autonome e versionate.

### 2.14 Consent requests

#### Tabella

`consent_requests`

#### Modello

`App\Models\ConsentRequest`

#### Scopo

Gestisce l’invio di una richiesta esterna tramite link tokenizzato, affinché il destinatario possa esprimere o aggiornare i consensi.

#### Campi verificati

Da un record reale risultano:

- id
- token
- owner_type
- owner_id
- contact_point_id
- created_by_user_id
- expires_at
- sent_at
- completed_at
- status
- source
- created_at
- updated_at

#### Stati previsti o usati

- pending
- inviato, se distinto da pending
- completed
- expired
- eventuale cancelled

#### Relazioni

- belongsTo ContactPoint
- riferimento polimorfico all’owner;
- belongsTo User tramite created_by_user_id .

#### Regole

- Il token deve essere casuale e non prevedibile.
- La richiesta ha una scadenza.
- Il link deve diventare inutilizzabile dopo il completamento.
- sent_at viene valorizzato quando l’email viene effettivamente inviata.
- completed_at viene valorizzato al completamento.
- La richiesta deve usare un recapito email valido.
- L’owner e il contact point devono essere coerenti con il destinatario.

### 2.15 Leads

#### Tabella pianificata

leads

#### Modello pianificato

`Lead`

Struttura più recente definita

- id
- organization_id , nullable
- person_id , nullable
- lead_status_id
- lead_source_id , nullable
- name
- description
- estimated_value
- expected_close_date
- assigned_user_id
- is_active
- timestamp

#### Tabelle correlate

- lead_statuses
- lead_sources

#### Nota sullo stato

Il lead era presente nel modello dati e nel prototipo React iniziale, compresa la conversione lead →
`organizzazione/persona.`

Non risulta però completato come modulo Laravel nella stessa misura di Organizations, People, Contact Points, Relations, Notes, Consents e WN+.

### 2.16 Custom fields

#### Tabelle pianificate

- custom_fields
- custom_field_options
- custom_field_values

`custom_fields`

- id
- name
- slug
- entity_type
- field_type
- organization_type_id , nullable
- is_required
- is_active
- sort_order
- timestamp

#### Tipi previsti

- text
- textarea
- number
- date
- boolean
- select
- multiselect

`custom_field_values`

Sono previsti campi tipizzati:

- value_text
- value_number

- value_date
- value_boolean
- value_json

#### Regole

- I campi personalizzati non sostituiscono i dati strutturali.
- Deve esistere un solo valore per la combinazione:
- custom_field_id ;
- owner_type ;
- owner_id .
- Deve essere valorizzata solo la colonna coerente con il field_type .

#### Stato

Pianificato nel modello dati, non risulta implementato nell’applicazione Laravel.

### 2.17 Logging

#### Tabelle pianificate

- audit_logs
- activity_logs
- access_logs

#### Audit log

Previsto per registrare:

- create;
- update;
- delete;
- valori precedenti;
- valori nuovi;
- utente responsabile;
- entità modificata.

#### Activity log

Previsto per registrare l’uso funzionale del sistema.

#### Access log

Previsto per:

- login riuscito;
- login fallito;
- logout;
- indirizzo IP;
- user agent.

#### Stato

La necessità del logging è stata definita anche nel documento di sicurezza, ma non risulta completato l’intero sottosistema con tutte e tre le tabelle.

## 3. Modello dati WN+
### 3.1 WN+ accounts

#### Tabella

`wn_plus_accounts`

#### Modello

`WnPlusAccount`

#### Scopo

Rappresenta gli utenti della piattaforma community WN+, separati dagli utenti interni del CRM.

#### Campi definiti

- id
- uuid
- organization_id
- person_id , nullable
- first_name
- last_name
- email
- password
- wn_plus_role_id
- wn_plus_level_id
- status
- account_type
- max_users
- invited_by_account_id , nullable
- created_by_user_id , nullable
- email_verified_at , nullable
- last_login_at , nullable
- timestamp

#### Stati

- pending
- active

Potrebbero essere aggiunti in futuro:

- suspended
- disabled

#### Tipi account

- manager
- user

#### Regole

- Il manager è il referente dell’organizzazione.
- Il manager può creare o invitare utenti.
- Il limite predefinito concordato per un manager è max_users = 8 .
- Un utente può essere collegato al manager che lo ha invitato tramite invited_by_account_id .
- L’account resta pending finché non completa:
- scelta password;
- verifica email;
- consensi obbligatori.
- La password è memorizzata tramite hashing Laravel.
- L’account WN+ è distinto da users , che contiene gli operatori CRM.

### 3.2 WN+ roles

#### Tabella

`wn_plus_roles`

#### Modello

`WnPlusRole`

#### Scopo

Ruoli applicativi trasmessi anche a WordPress tramite OIDC.

Sono stati predisposti seeder per i ruoli.

### 3.3 WN+ levels

#### Tabella

`wn_plus_levels`

#### Modello

`WnPlusLevel`

#### Scopo

Rappresenta il livello o piano di accesso dell’utente WN+.

Sono stati predisposti seeder e il valore viene trasmesso a WordPress tramite claim OIDC.

### 3.4 WN+ invitations

#### Tabella

`wn_plus_invitations`

#### Modello

`WnPlusInvitation`

#### Campi

- id
- account_id
- token
- expires_at
- sent_at
- accepted_at
- timestamp

#### Regole

- Il token è lungo e casuale, indicativamente 64 caratteri.
- La durata dell’invito è di 7 giorni.
- L’invito accettato non può essere riutilizzato.
- Il destinatario imposta personalmente la password.
- Dopo il completamento l’account passa da pending ad active .

### 3.5 OIDC clients

#### Tabella

`wn_plus_oidc_clients`

#### Scopo

Registra i client autorizzati a utilizzare il provider OpenID Connect del CRM.

È stato configurato il client WordPress usato dal plugin OpenID Connect Generic Client.

## 4. Moduli e funzionalità effettivamente implementate
### 4.1 Autenticazione CRM
Risultano implementati:

- login;
- route protette con middleware auth ;
- redirect alla dashboard;
- logout;
- layout autenticato;
- navigazione laterale e topbar;
- gestione della voce di menu attiva.

La prima versione usava Laravel Fortify; il progetto successivo ha mantenuto il concetto di utenti CRM separati dagli account WN+.

### 4.2 Layout e design system CRM
È stato realizzato un layout Blade basato su Bootstrap 5 con:

- sidebar;
- topbar;
- area contenuto;
- tabelle coerenti;
- card;
- dropdown per azioni;
- modali;
- badge;
- icone SVG.

Componenti Blade creati o consolidati:

- <x-crm.button>
- <x-crm.row-actions>
- <x-icon>

Sono state uniformate:

- dimensioni dei pulsanti;
- varianti cromatiche;
- icone;
- menu a tre puntini;
- spaziature;

- intestazioni delle tabelle;
- stati vuoti;
- messaggi flash.

È stata sviluppata una libreria di icone SVG usata trasversalmente.

### 4.3 Organizations
Il vecchio modulo Client è stato superato dal modulo Organizations.

Risultano sviluppati:

- elenco organizzazioni;
- creazione;
- modifica;
- eliminazione o disattivazione;
- ricerca;
- ordinamento;
- paginazione;
- messaggi di conferma;
- visualizzazione del tipo di organizzazione;
- visualizzazione dei ruoli;
- badge di classificazione;
- pagina di dettaglio;
- collegamento alle persone associate;
- collegamento ai recapiti;
- collegamento agli indirizzi;
- collegamento alle note.

La UI delle organizzazioni è stata progressivamente uniformata alla UI delle persone.

### 4.4 People
Risultano implementati:

- elenco persone;
- creazione;
- modifica;
- pagina di dettaglio;
- eliminazione;
- ricerca;
- ordinamento;
- paginazione;
- visualizzazione delle relazioni con le organizzazioni;
- recapiti personali;
- note;
- badge e riepiloghi.

È stata mantenuta la distinzione tra:

- dati personali generali;
- dati legati a una specifica organizzazione.

### 4.5 Person–organization relations
Il modulo relazioni è stato implementato su entrambe le direzioni:

- dalla pagina persona;
- dalla pagina organizzazione.

Funzionalità sviluppate:

- elenco delle relazioni;
- card dedicate;
- creazione;
- modifica tramite modale;
- eliminazione;
- visualizzazione qualifica;
- visualizzazione dipartimento;
- stato attivo;
- menu a tre puntini;
- fix della route o logica destroy ;
- allineamento del comportamento tra Organizations e People.

La relazione è stata trattata come modello autonomo e non come una semplice pivot Laravel.

### 4.6 Contact points
Questo è uno dei moduli maggiormente sviluppati.

Funzionalità implementate:

- gestione polimorfica dei recapiti;
- recapiti di organizzazione;
- recapiti di persona;
- recapiti della relazione persona-organizzazione;
- creazione tramite modale;
- modifica tramite modale;
- eliminazione;
- tipo;
- utilizzo;
- etichetta;
- valore;
- recapito principale;
- recapito attivo;
- ordinamento visuale;

- icone automatiche;
- pulsante copia;
- link email;
- link telefonico;
- apertura sito o social;
- indicatori di consenso collegati al recapito.

È stato creato un partial Blade _list.blade.php o equivalente per riutilizzare la lista dei contact point nei diversi owner.

Sono state gestite le tabelle di configurazione:

- contact_types ;
- contact_usages .

Il precedente concetto di contact_channels è stato eliminato a favore di contact_types.category.

### 4.7 Copia globale dei valori
È stata introdotta una funzione JavaScript globale per copiare negli appunti valori come:

- email;
- telefono;
- URL;
- altri recapiti.

Il feedback visivo viene gestito in modo uniforme.

### 4.8 Addresses
Il modello e la struttura polimorfica degli indirizzi sono stati definiti.

L’integrazione nelle schede anagrafiche è stata almeno parzialmente realizzata.

Non risulta però che la gestione UI degli indirizzi abbia raggiunto lo stesso livello di rifinitura dei contact point.

### 4.9 Notes
È stato implementato un modulo note riutilizzabile.

Funzionalità:

- apertura tramite modale;
- elenco cronologico;

- creazione;
- modifica o gestione;
- archiviazione;
- ripristino;
- nota fissata in evidenza;
- eliminazione definitiva;
- associazione polimorfica;
- autore;
- data.

Le note sono state integrate nelle principali pagine CRM.

### 4.10 Consents
Sono stati implementati:

- migration consent_types ;
- migration consent_versions ;
- migration consents ;
- aggiunta della categoria ai tipi;
- aggiunta di:
- requested_at ;
- denied_at ;
- created_by_user_id ;
- notes ;
- evidence_file_path ;
- indici;
- model e relazioni;
- seeder dei tipi;
- seeder delle versioni;
- rappresentazione dello stato tramite badge;
- visualizzazione dei consensi nella scheda persona o recapito;
- gestione generalizzata delle funzioni:
- consentBadgeVariant ;
- consentStatusLabel .

Stati rappresentati in UI:

- positivo/concesso;
- pendente;
- negato;
- revocato, dove applicabile.

Sono stati predisposti i tipi:

- informativa privacy;
- comunicazioni promozionali;
- utilizzo immagini.

### 4.11 Consent requests
Il sottosistema per richiedere consensi tramite email è stato avviato e in buona parte sviluppato.

Risultano presenti:

- model ConsentRequest ;
- migration;
- token;
- owner polimorfico;
- contact point;
- creatore;
- scadenza;
- stato;
- date di invio e completamento;
- ConsentRequestService ;
- metodo o flusso per l’invio;
- Mailable ConsentRequestMail ;
- configurazione SMTP;
- test via Tinker.

Problemi affrontati:

1. inizialmente mancava ConsentRequestService::send() ;
2. successivamente Laravel restituiva: InvalidArgumentException: View [view.name] not
found;
3. è stato creato o modificato app/Mail/ConsentRequestMail.php ;
4. resta da verificare definitivamente il riferimento alla view Blade reale nel Mailable.

Il flusso non può quindi essere considerato completamente chiuso, pur essendo strutturalmente impostato.

### 4.12 WN+ accounts e inviti
È stato implementato il modulo WN+ con:

- account;
- ruoli;
- livelli;
- inviti;
- ricerca organizzazione;
- ricerca referente;
- selezione dell’email;
- scelta tra email personali e legate alla relazione;
- creazione account manager;
- creazione account utente;
- limite utenti;
- associazione dell’utente al manager invitante;
- token di invito;

- scadenza;
- pagina pubblica di accettazione;
- impostazione password;
- acquisizione consensi;
- attivazione account.

È stata verificata in Tinker almeno una registrazione reale con:

- account attivo;
- ruolo user ;
- livello valorizzato;
- invited_by_account_id ;
- created_by_user_id ;
- invito con accepted_at valorizzato.

È stato creato un layout dedicato:

- resources/views/layouts/wn-plus.blade.php

con CSS dedicato per il flusso di accettazione.

Sono stati corretti:

- titolo;
- testo dell’invito;
- form password;
- sezione consensi;
- pulsante principale;
- stile generale.

Gli asset compilati sono stati inclusi nel repository perché sul server di produzione non è disponibile
`Node/npm.`

### 4.13 Email WN+
È stato implementato l’invio SMTP:

- mittente info@welfarenest.it ;
- SMTP Gmail Relay autenticato;
- test da Tinker riuscito;
- invio degli inviti WN+.

È presente almeno il Mailable:

- WnPlusInvitationMail.php

Il recapito e il flusso reale degli inviti risultano funzionanti.

### 4.14 OpenID Connect / SSO WordPress
È stato implementato nel CRM un provider OIDC dedicato a WN+.

Controller principale:

- WnPlusOidcController

Endpoint predisposti:

- discovery;
- authorize;
- token;
- userinfo;
- JWKS.

Discovery document con:

- issuer ;
- authorization_endpoint ;
- token_endpoint ;
- userinfo_endpoint ;
- jwks_uri ;
- scope supportati;
- claim supportati.

Scope:

- openid
- profile
- email

Claim inviati a WordPress:

- sub
- name
- given_name
- family_name
- email
- email_verified
- wn_role
- wn_level
- organization_id

Sono stati affrontati e risolti problemi relativi a:

- permessi sulla chiave privata;
- errore invalid-user-claim ;
- token non disponibile nel metodo userinfo ;
- memorizzazione temporanea del token tramite cache;
- assenza iniziale del metodo userinfo ;

- compatibilità con il plugin OpenID Connect Generic Client di WordPress.

È stata inserita una logica simile a:

Cache::put('wn_plus_access_token_'. $accessToken, $account->id, now()- >addHour());

per collegare l’access token all’account.

Dopo le correzioni il login SSO è risultato funzionante.

### 4.15 Separazione sessioni CRM e WN+
È stato chiarito che:

- il CRM e WordPress hanno sessioni separate;
- effettuare logout da WordPress non chiude automaticamente la sessione CRM;
- effettuare logout dal CRM non chiude necessariamente WordPress.

Non è stato implementato un single logout completo.

### 4.16 Server e deployment
Ambiente di sviluppo server:

- Ubuntu 24.04;
- Apache;
- PHP 8.3;
- MariaDB;
- Composer;
- Node 20;
- npm;
- repository in /mnt/dati/web/crm ;
- virtual host crm.reload.lan ;
- Adminer su db.reload.lan ;
- accesso SSH;
- Tailscale;
- share Samba.

Produzione:

- CRM su https://crm.welfarenest.it ;
- WN+ su https://plus.welfarenest.it ;
- server VM in Italia;
- asset Vite compilati inclusi nel repository;
- build effettuata prima del deploy;
- gestione dei permessi di:
- storage ;

- bootstrap/cache ;
- chiavi OIDC.

## 5. Moduli pianificati ma non completati
### 5.1 Leads
Il modello è definito, ma manca un modulo Laravel completo comprendente:

- CRUD;
- pipeline;
- filtri per stato;
- assegnazione operatore;
- fonte;
- valore stimato;
- data prevista chiusura;
- conversione in organizzazione/persona/relazione;
- trasferimento dei consensi;
- deduplicazione durante la conversione.

### 5.2 Attività, task e scadenze
Non è stato ancora realizzato un vero modulo attività.

Mancano:

- task;
- promemoria;
- scadenze;
- appuntamenti;
- follow-up;
- assegnazioni;
- stato;
- priorità;
- notifiche.

Le note non devono essere utilizzate come sostituto di questo modulo.

### 5.3 Custom fields
Il modello è stato progettato ma non implementato.

Mancano:

- migration;

- modelli;
- CRUD amministrativo;
- rendering dinamico nei form;
- salvataggio tipizzato;
- filtri;
- validazione;
- limitazione per tipo di organizzazione.

### 5.4 Logging completo
Mancano o sono incompleti:

- audit automatico dei model;
- activity log;
- access log;
- conservazione e consultazione;
- interfaccia amministrativa;
- politiche di retention;
- tracciamento delle cancellazioni;
- tracciamento delle modifiche ai consensi.

### 5.5 Ruoli e permessi utenti CRM
L’autenticazione è presente, ma il sistema autorizzativo non risulta completato.

Mancano:

- ruoli formalizzati;
- permission;
- policy;
- gate;
- profili come:
- amministratore;
- operatore;
- sola lettura;
- gestione WN+;
- gestione consensi;
- schermata completa di amministrazione utenti.

La documentazione di sicurezza ipotizza 2 amministratori e 6 operatori.

### 5.6 Addresses UI completa
Restano da uniformare:

- CRUD in modale;

- partial riutilizzabile;
- badge indirizzo principale;
- azione copia;
- link mappa;
- ordinamento;
- validazione CAP/provincia/paese;
- gestione coerente da persona, organizzazione e relazione.

### 5.7 Consent requests completo
Restano da chiudere:

- view email corretta;
- test end-to-end;
- pagina pubblica della richiesta;
- compilazione dei singoli consensi;
- aggiornamento atomico di consents ;
- scadenza automatica;
- reinvio;
- annullamento;
- storico richieste;
- badge nella UI;
- gestione link già completato o scaduto;
- job o command per segnare gli scaduti;
- prova dell’invio e dell’accettazione.

### 5.8 Pagine di riepilogo consensi
Era prevista una distinzione tra:

- consensi positivi;
- consensi pendenti;
- consensi negati o revocati;
- richieste inviate.

La visualizzazione è stata avviata nelle schede, ma manca un vero centro consensi trasversale.

### 5.9 WN+ amministrazione completa
Restano da sviluppare o consolidare:

- elenco account;
- filtri per organizzazione, stato, ruolo e livello;
- sospensione;
- riattivazione;
- reinvio invito;

- revoca invito;
- modifica limite utenti;
- trasferimento manager;
- gestione manager cessato;
- gestione utenti orfani;
- storico login;
- ultimo accesso;
- reset password;
- disconnessione forzata;
- audit;
- pagina account dal lato manager;
- enforcement definitivo del limite max_users .

### 5.10 Single logout OIDC
Non ancora implementato:

- logout centralizzato;
- revoca token;
- eventuale endpoint end_session_endpoint ;
- sincronizzazione logout WordPress/CRM.

### 5.11 Newsletter
Il prototipo iniziale prevedeva:

- liste;
- segmenti;
- invii spot;
- storico campagne;
- statistiche.

Non risulta implementato nel CRM Laravel reale.

### 5.12 Report ed esportazioni
Il prototipo prevedeva:

- esportazione contatti;
- esportazione organizzazioni;
- filtri;
- Excel;
- CSV.

Non risulta completato nel progetto Laravel.

### 5.13 Cestino generale
Il prototipo iniziale prevedeva un cestino applicativo con ripristino.

Nel progetto Laravel reale alcune entità hanno archiviazione o cancellazione, ma non risulta un cestino globale uniforme per:

- organizzazioni;
- persone;
- relazioni;
- recapiti;
- note;
- account WN+.

## 6. Decisioni architetturali
### 6.1 Separazione Organization / Person
È una decisione centrale.

Organization rappresenta un soggetto amministrativo.

Person rappresenta un individuo.

Non si deve usare una sola tabella “contacts” per contenere entrambi.

Motivi:

- dati differenti;
- relazioni differenti;
- gestione amministrativa;
- storico degli incarichi;
- recapiti personali e lavorativi distinti;
- maggiore normalizzazione.

### 6.2 Relazione persona-organizzazione come entità
Non è una pivot minima.

Contiene:

- qualifica;
- dipartimento;
- date;
- stato;
- recapiti;

- eventualmente indirizzi e note.

Questo consente di rappresentare correttamente il contesto lavorativo.

### 6.3 Polimorfismo controllato
Il progetto utilizza coppie:

- owner_type
- owner_id

Ma owner_type non deve contenere il nome completo della classe Laravel.

Valori corretti:

- organization
- person
- person_organization_relation
- lead
- wn_plus_account

Valori da evitare:

- App\Models\Person
- App\Models\Organization

Motivi:

- stabilità del database;
- indipendenza dai namespace;
- leggibilità;
- portabilità;
- controllo sui valori ammessi.

Per ottenere questo comportamento è opportuno usare una morph map Laravel dichiarata centralmente.

### 6.4 Recapiti centralizzati
Tutti i recapiti devono stare in contact_points.

Non devono esistere colonne duplicate come:

- email in people ;
- phone in organizations ;
- work_email nelle relazioni.

Motivi:

- recapiti multipli;
- recapiti primari;
- storico;
- attivazione/disattivazione;
- tipizzazione;
- consenso sul singolo canale.

### 6.5 Indirizzi centralizzati
Tutti gli indirizzi devono stare in addresses.

Non si devono aggiungere colonne come:

- legal_address ;
- operational_city ;
- shipping_address ;

nelle entità principali.

### 6.6 Consensi come eventi, non booleani
Un consenso non può essere rappresentato da:

- privacy = true ;
- marketing = false .

Occorrono:

- tipo;
- versione;
- stato;
- data;
- fonte;
- owner;
- operatore;
- prova;
- eventuale revoca.

Questa scelta garantisce tracciabilità legale.

### 6.7 Semplificazione contact types
È stata abbandonata la separazione:

- tipo generale;

- canale specifico.

È stata adottata:

- una sola tabella contact_types ;
- un campo category .

Questo riduce join e complessità senza perdere la possibilità di associare comportamenti comuni.

### 6.8 Account CRM separati da account WN+
users serve agli operatori del CRM.

wn_plus_accounts serve agli utenti esterni della community.

Motivi:

- autenticazione distinta;
- ruoli distinti;
- lifecycle distinto;
- consensi distinti;
- sicurezza;
- nessuna necessità di dare accesso al CRM agli utenti WN+.

### 6.9 Inviti invece di password impostate dall’operatore
L’operatore crea l’account in stato pending.

Il destinatario riceve un token e imposta personalmente la password.

Motivi:

- nessuna password comunicata via email;
- migliore sicurezza;
- verifica del possesso dell’indirizzo;
- accettazione dei consensi nel medesimo flusso.

### 6.10 Service layer per flussi complessi
Per operazioni come:

- creazione invito;
- invio email;
- richiesta consenso;
- attivazione account;

è stata scelta o avviata una separazione dai controller tramite classi service, ad esempio:

- ConsentRequestService .

Il controller dovrebbe coordinare richiesta e risposta; il service dovrebbe contenere la logica applicativa.

### 6.11 Componenti Blade riutilizzabili
Le parti ricorrenti devono essere componenti o partial:

- pulsanti;
- icone;
- azioni riga;
- badge;
- lista recapiti;
- lista note;
- form modali;
- card relazioni.

Questo evita divergenze visive e codice duplicato.

### 6.12 Asset compilati in repository
Poiché il server di produzione non dispone di npm, è stato deciso di versionare public/build.

Conseguenze:

- eseguire npm run build prima del commit destinato alla produzione;
- non ignorare completamente public/build ;
- verificare il manifest Vite;
- evitare modifiche manuali ai file compilati.

## 7. Convenzioni stabilite
### 7.1 Lingua

Database e codice

Inglese.

Esempi:

- organizations
- person_id
- contact_points

- is_active
- granted_at

Interfaccia utente

Italiano.

Esempi:

- Organizzazioni
- Persone
- Recapiti
- Qualifica
- Consenso acquisito
- Invito inviato

Questa convenzione è formalizzata nel glossario.

### 7.2 Naming database
- Tabelle al plurale.
- Modelli al singolare.
- snake_case .
- Foreign key in forma *_id .
- Booleani con prefisso is_ , quando descrivono uno stato:
- is_active
- is_primary
- is_pinned
- is_split_payment
- Date evento con suffisso _at :
- granted_at
- requested_at
- sent_at
- accepted_at
- completed_at
- expires_at
- Codici tecnici in code .
- Ordinamento manuale in sort_order .

### 7.3 Tabelle di classificazione
Seguono in genere lo schema:

- id
- code
- name
- description
- is_active

- sort_order
- timestamp

code è la chiave tecnica stabile.

name è l’etichetta mostrata in italiano.

### 7.4 Model Laravel
Per ogni modello:

- $fillable esplicito o uso controllato di $guarded ;
- $casts per:
- booleani;
- date;
- datetime;
- JSON;
- relazioni nominate in modo descrittivo;
- scope per filtri ricorrenti, per esempio:
- active() ;
- ordered() ;
- pending() ;
- niente logica HTML nei model;
- niente query complesse replicate nelle view.

### 7.5 Controller
Pattern raccomandato:

- controller resource;
- metodi brevi;
- validazione tramite FormRequest;
- logica complessa delegata a service;
- redirect con messaggio flash;
- route model binding;
- autorizzazione tramite policy.

Per nuove feature evitare controller molto estesi che gestiscano insieme:

- validazione;
- salvataggio;
- invio email;
- token;
- rendering;
- business rule.

### 7.6 Migration
- Una migration per cambiamento logico.
- Foreign key esplicite.
- Indici sui campi usati nei filtri.
- Vincoli univoci ove necessario.
- Ordine di creazione coerente con le dipendenze.
- Metodo down() realmente reversibile, quando possibile.
- Attenzione alla compatibilità tra SQLite e MariaDB/MySQL.
- Non modificare vecchie migration già eseguite in produzione: creare migration incrementali.

### 7.7 Validazione
Le validazioni devono essere centralizzate nei FormRequest quando il form diventa stabile.

Esempi:

- organization:
- almeno uno tra name e legal_name ;
- relation:
- end_date >= start_date ;
- contact point:
- formato coerente con contact_type.category ;
- consent:
- versione coerente con tipo;
- invitation:
- email valida;
- account non già attivo;
- token non scaduto;
- WN+:
- limite manager;
- organizzazione coerente;
- email non duplicata secondo la regola scelta.

### 7.8 UI
- Azioni riga tramite menu coerente.
- Pulsanti tramite <x-crm.button> .
- Icone tramite <x-icon> .
- Tabelle con:
- ricerca;
- ordinamento;
- paginazione;
- stato vuoto;
- Modali per entità secondarie:
- recapiti;
- relazioni;

- note;
- Pagine dedicate per entità principali:
- organizzazioni;
- persone;
- account WN+.
- Badge con testi e colori centralizzati.
- Evitare di duplicare condizioni dei badge nelle singole view.

## 8. Punti aperti e decisioni da chiarire
### 8.1 Stato reale del modello rispetto alla documentazione
I documenti 0.6 e 0.7 non comprendono tutte le modifiche successive.

Serve produrre un modello dati 0.8 o 1.0 che includa:

- WN+;
- consent_requests ;
- campi aggiunti a consents ;
- campi aggiunti a consent_types ;
- eventuali soft delete;
- stato effettivo di Notes;
- morph map definitiva.

### 8.2 Codici definitivi dei consensi
Nei documenti compaiono:

- privacy_base
- marketing

Nel codice più recente:

- privacy_notice
- promotional_emails
- image_disclosure

È opportuno dichiarare ufficialmente i codici correnti ed eliminare eventuali residui dei precedenti.

### 8.3 Owner dei consensi
Da chiarire una matrice definitiva:

- informativa privacy → person , lead o wn_plus_account ;
- marketing → contact_point oppure soggetto;

- immagini → person o wn_plus_account ;
- consensi relativi al rapporto → eventualmente person_organization_relation .

Questa decisione incide sulle query e sulla UI.

### 8.4 Unicità email WN+
Da definire se l’email deve essere:

- globalmente univoca;
- univoca per organizzazione;
- riutilizzabile per più account con ruoli differenti.

Per OIDC e WordPress è fortemente consigliata l’unicità globale dell’account.

### 8.5 Persona e account WN+
person_id è nullable.

Occorre decidere se:

- ogni account debba obbligatoriamente essere collegato a una persona;
- gli account creati con dati manuali debbano generare una persona;
- sia ammesso un account senza anagrafica CRM.

La soluzione più coerente nel lungo periodo è collegare ogni account a una persona, evitando duplicazione di nome e cognome; tuttavia i campi denormalizzati nell’account possono essere utili come snapshot.

### 8.6 Manager multipli
Da chiarire:

- una organizzazione può avere più manager?
- il limite max_users è per singolo manager o per organizzazione?
- un utente può essere gestito da più manager?
- cosa avviene quando il manager viene disattivato?

### 8.7 Cancellazione dati e retention
Nel documento di sicurezza rimanevano da definire:

- durata dei log;
- durata delle richieste consenso;
- durata degli account WN+ inattivi;

- cancellazione dati dopo revoca;
- anonimizzazione;
- gestione dei backup dopo richiesta di cancellazione.

### 8.8 Consent request Mailable
È aperto il problema della view non trovata.

Occorre verificare:

- ConsentRequestMail::content() ;
- eventuale ->view('...') ;
- percorso reale in resources/views ;
- nome della view;
- eventuale uso errato del placeholder letterale view.name ;
- cache delle view.

### 8.9 Cache degli access token OIDC
La soluzione basata su Cache::put() funziona, ma bisogna decidere se sia sufficiente in produzione.

Da valutare:

- cache condivisa;
- Redis;
- tabella token;
- revoca;
- scadenza;
- riavvio server;
- più istanze applicative;
- tracciamento sicurezza.

### 8.10 Chiavi OIDC
Sono già emersi problemi di permessi sul file:

`storage/app/oidc/private.key`

Serve documentare:

- generazione;
- owner;
- gruppo;
- permessi;
- backup;

- rotazione;
- pubblicazione JWKS;
- gestione chiave diversa tra sviluppo e produzione.

### 8.11 Ruoli OIDC e WordPress
Da formalizzare la corrispondenza:

- wn_role CRM → ruolo WordPress;
- wn_level CRM → capability o livello contenuti;
- comportamento quando ruolo o livello cambiano;
- aggiornamento automatico a ogni login;
- disabilitazione account WordPress quando l’account CRM viene sospeso.

## 9. Prossimi passi suggeriti
Priorità 1 — Chiudere le richieste consenso
1. Correggere ConsentRequestMail .
2. Creare la view email definitiva.
3. Verificare invio via Gmail Relay.
4. Creare o verificare la pagina pubblica del token.
5. Salvare le risposte in transazione.

6. Aggiornare:

7. status ;

8. completed_at ;
9. record consents .

10. Gestire link:

11. scaduto;

12. già completato;
13. invalido.
14. Aggiungere reinvio e annullamento dalla UI.
15. Integrare la richiesta nella scheda persona e nella scheda recapito.

Priorità 2 — Consolidare WN+
1. Realizzare elenco amministrativo account.
2. Aggiungere filtri e badge.
3. Aggiungere reinvio inviti.

4. Aggiungere sospensione account.
5. Applicare realmente il limite max_users .
6. Gestire manager e utenti invitati.
7. Mostrare ultimo login.
8. Verificare aggiornamento claim OIDC.
9. Documentare il payload JSON inviato a WordPress.
10. Valutare revoca e persistenza token più robusta.

Priorità 3 — Aggiornare la documentazione del database Produrre una versione consolidata crm_data_model_v08.md o v1.0 basata sulle migration reali.

Dovrebbe comprendere:

- tutte le tabelle CRM;
- WN+;
- consensi;
- richieste consenso;
- indici;
- unique;
- foreign key;
- cascade;
- owner type ammessi;
- enum o stati controllati;
- stato implementato di ogni tabella.

Priorità 4 — Autorizzazioni utenti CRM
1. Definire ruoli.
2. Creare permission.
3. Implementare policy.

4. Proteggere:

5. cancellazioni;

6. gestione consensi;
7. gestione WN+;
8. configurazioni;
9. gestione utenti.
10. Registrare le operazioni sensibili.

Priorità 5 — Audit log Iniziare almeno da:

- organizzazioni;
- persone;
- relazioni;
- contact point;
- consensi;
- account WN+.

Registrare:

- autore;
- azione;
- timestamp;
- vecchi valori;
- nuovi valori.

Priorità 6 — Rifinitura moduli core Uniformare completamente:

- addresses;
- relations;
- contact points;
- notes;
- consent badge;
- azioni riga;
- modali;
- validazioni;
- messaggi di errore.

Priorità 7 — Lead e attività Dopo la stabilizzazione dell’anagrafica:

1. Implementare Leads.
2. Implementare conversione.
3. Implementare attività/task.

4. Collegare follow-up a:

5. persona;

6. organizzazione;
7. lead;
8. relazione.

9. Aggiungere scadenze e assegnatari.

## 10. Ordine consigliato per riprendere il progetto su un altro strumento
Prima di modificare il codice, il nuovo strumento dovrebbe ricevere:

1. questo documento;
2. tutte le migration;
3. i model;
4. routes/web.php ;
5. i controller dei moduli core;
6. i componenti Blade;
7. i service;
8. i Mailable;
9. i seeder;
10. il controller OIDC;
11. le configurazioni WN+;
12. un dump dello schema del database, privo di dati personali;
13. l’elenco delle route tramite:

php artisan route:list

1. l’elenco delle migration tramite:

php artisan migrate:status

1. le versioni installate tramite:

php artisan about

Il nuovo strumento dovrà considerare le migration e il codice effettivo come fonte primaria, mentre i documenti 0.6 e 0.7 devono essere trattati come documentazione storica da aggiornare.
