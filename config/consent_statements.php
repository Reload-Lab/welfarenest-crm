<?php

/*
|--------------------------------------------------------------------------
| Dichiarazioni mostrate accanto ai checkbox dei consensi
|--------------------------------------------------------------------------
|
| Testi approvati dalla DPO, indicizzati per `consent_types.code`, usati dal
| form pubblico di raccolta consensi (resources/views/consent-requests/) e
| dal portale WN+ (resources/views/wn-plus/).
|
| Stanno qui e non a database perché sono formulazioni di interfaccia
| (prima persona, rivolte al destinatario), mentre `consent_types.name` e
| `.description` restano le etichette brevi usate nel back office.
|
| Si mettono qui SOLO formulazioni fornite dalla DPO. Dalla revisione del
| 24/09/2026 le informative PDF non riportano più il testo dei consensi
| (vedi claude/informative-pdf-20260924.md), quindi questo file è l'unica
| sede di quelle formulazioni e non può contenere testi approssimati.
|
| RISOLUZIONE (leggere sempre tramite App\Support\ConsentStatement::for()):
|
|   1. versions[<version_code>][<code>]  — testo specifico dell'informativa
|   2. <code>                            — testo valido per tutti i contesti
|   3. titolo della ConsentVersion
|   4. nome del ConsentType
|
| Il livello 1 serve perché lo stesso consenso è formulato diversamente a
| seconda dell'informativa: `privacy_notice` è generico nella 01 e "presente
| informativa" nelle 12/13, e la visibilità di email e telefono è scritta in
| due modi diversi tra referente e membro. Aggiungere un consenso senza
| testo non rompe nulla: si ricade sull'etichetta breve.
|
| Restano da recuperare i consensi immagini degli ALTRI contesti (singolo
| evento nelle 03/03bis, una tantum nelle 04 e 07-11): nei PDF non erano un
| consenso solo, e probabilmente serviranno più consent_type invece di uno.
| I testi originali sono nei PDF pre-revisione conservati in
| "Claude outputs\consensi-sostituiti-20260924" — non cancellarli finché
| non sono tutti qui. Quelli delle informative 12 e 13 sono stati recuperati
| da lì il 29/09/2026 e sono riportati sotto alla lettera.
|
*/

$wnPlusCommon = [

    'privacy_notice' => 'Dichiaro di avere letto la presente informativa.',

    'profile_visibility_basic' => 'Acconsento a rendere visibili agli altri utenti Welfare Nest Plus il mio nome, cognome e l’azienda di appartenenza.',

    'profile_visibility_photo' => 'Acconsento a rendere visibile agli altri utenti la fotografia caricata nel mio profilo.',

    'service_updates' => 'Acconsento a ricevere avvisi non strettamente necessari relativi a nuove funzionalità e contenuti dell’area riservata.',

    'image_disclosure' => 'Acconsento all’uso e alla divulgazione di immagini individualizzate che mi riguardano, quali primi piani, interviste, testimonianze, fotografie posate o immagini nelle quali sono il soggetto principale, per campagne promozionali e per la pubblicazione sui canali social ufficiali e nella rivista cartacea o digitale di Welfare Nest. Il consenso vale anche quale liberatoria ai sensi degli artt. 10 c.c. e 96 della legge 22 aprile 1941, n. 633 e, limitatamente ai contenuti da me espressi nel corso delle riprese, quale licenza d’uso non esclusiva e gratuita per le medesime finalità. Welfare Nest non potrà cedere a terzi l’immagine o i contenuti per autonome finalità senza un ulteriore idoneo presupposto. La scelta è una tantum e vale fino a revoca.',

    'identifiable_surveys' => 'Acconsento al trattamento delle mie risposte per survey personali identificabili relative alla community e alla soddisfazione dei servizi.',

];

return [

    'privacy_notice' => 'Dichiaro di aver letto l’informativa sul primo contatto e sull’inserimento dei miei dati nel CRM.',

    'promotional_emails' => 'Acconsento a ricevere tramite newsletter comunicazioni commerciali, offerte e inviti di Welfare Nest.',

    'versions' => [

        // Informativa 12 — referente WN+. Email e telefono usano la forma
        // "Questa scelta è attivabile soltanto se…".
        '12_referente_wnplus_2026_v1' => $wnPlusCommon + [

            'profile_visibility_email' => 'Acconsento a rendere visibile agli altri utenti il mio indirizzo email professionale. Questa scelta è attivabile soltanto se è visibile il profilo base.',

            'profile_visibility_phone' => 'Acconsento a rendere visibile agli altri utenti il mio numero di telefono. Questa scelta è attivabile soltanto se è visibile il profilo base.',

        ],

        // Informativa 13 — membro WN+. Identica alla 12 tranne email e
        // telefono, dove la DPO ha usato la forma "purché sia attiva…".
        '13_membro_wnplus_2026_v1' => $wnPlusCommon + [

            'profile_visibility_email' => 'Acconsento a rendere visibile agli altri utenti il mio indirizzo email professionale, purché sia attiva la visibilità del profilo base.',

            'profile_visibility_phone' => 'Acconsento a rendere visibile agli altri utenti il mio numero di telefono, purché sia attiva la visibilità del profilo base.',

        ],

    ],

];
