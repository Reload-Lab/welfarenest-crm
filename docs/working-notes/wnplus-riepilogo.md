# Stato del progetto CRM Welfare Nest / WN+

## Panoramica

In questa fase di sviluppo sono stati affrontati due filoni principali:

1.  **WN+ (Identity Provider e gestione account)**
2.  **Sistema generale dei consensi del CRM**

L'obiettivo è far diventare il CRM il punto centrale per la gestione di
identità, accessi, relazioni e consensi.

------------------------------------------------------------------------

# 1. WN+ -- Stato attuale

## Completato

-   Gestione account Manager/User con organization, role, level,
    invited_by_account, max_users e status.
-   Sistema di inviti completo: CRM → Account → Email → Token → Password
    → Consensi → Attivazione → Login SSO.
-   SMTP Google Workspace Relay configurato e funzionante.
-   Layout dedicato (`layouts/wn-plus.blade.php`) e CSS dedicato.
-   Refactoring della pagina account con partial.
-   SSO OpenID Connect funzionante tra CRM e WordPress.

## Da sviluppare

-   Inviti dei nuovi utenti da parte del referente (manager).
-   Nessun Single Logout: WordPress gestirà il logout indipendentemente
    dal CRM.

------------------------------------------------------------------------

# 2. Sistema Consensi CRM

## Esistente

-   ConsentType
-   ConsentVersion
-   Consent
-   ConsentService con grant(), deny(), revoke().

## Nuova infrastruttura

-   Tabella `consent_requests`
-   Model `ConsentRequest`
-   Service `ConsentRequestService`
-   Controller pubblico `ConsentRequestController`
-   Rotta pubblica `/consent-requests/{token}`
-   Prima view pubblica.

## Integrazione

Alla creazione di un nuovo ContactPoint email viene generata
automaticamente una ConsentRequest.

------------------------------------------------------------------------

# Stato attuale

L'infrastruttura è quasi completata.

Il punto rimasto da completare è il Mailable `ConsentRequestMail`, che
deve utilizzare la view:

`emails.consent-request`

al posto del placeholder generato da Laravel (`view.name`).

------------------------------------------------------------------------

# Prossimi passi

1.  Completare `ConsentRequestMail`.
2.  Testare l'invio reale delle email.
3.  Realizzare la pagina pubblica definitiva per la raccolta dei
    consensi.
4.  Collegare la conferma al `ConsentService`.
5.  Mostrare nel CRM lo stato della richiesta (inviata, scaduta,
    reinviabile).

------------------------------------------------------------------------

# Visione

Il CRM sta evolvendo in una piattaforma centrale di gestione di:

-   Organizzazioni
-   Persone
-   Relazioni
-   Recapiti
-   Note
-   Consensi
-   Identità
-   Single Sign-On
-   Account WN+
-   Workflow di onboarding e gestione consensi
