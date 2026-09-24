<?php

/*
|--------------------------------------------------------------------------
| Dichiarazioni mostrate accanto ai checkbox dei consensi
|--------------------------------------------------------------------------
|
| Testi approvati dalla DPO per il form pubblico di raccolta consensi
| (resources/views/consent-requests/show.blade.php), indicizzati per
| `consent_types.code`.
|
| Stanno qui e non a database perché sono formulazioni di interfaccia
| (prima persona, rivolte al destinatario), mentre `consent_types.name` e
| `.description` restano le etichette brevi usate nel back office.
| Un codice non presente qui ricade sul titolo della versione informativa
| e, in mancanza, sul nome del tipo di consenso: aggiungere un nuovo
| consenso non rompe la pagina, al massimo mostra l'etichetta breve.
|
*/

return [

    'privacy_notice' => 'Dichiaro di aver letto l’informativa sul primo contatto e sull’inserimento dei miei dati nel CRM.',

    'promotional_emails' => 'Acconsento a ricevere tramite newsletter comunicazioni commerciali, offerte e inviti di Welfare Nest.',

    'image_disclosure' => 'Acconsento all’utilizzo e alla divulgazione delle immagini e del materiale fotografico che mi riguardano.',

];
