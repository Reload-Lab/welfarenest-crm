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
| Si mettono qui SOLO formulazioni fornite dalla DPO. Dalla revisione del
| 24/09/2026 le informative PDF non riportano più il testo dei consensi
| (vedi claude/informative-pdf-20260924.md), quindi questo file è l'unica
| sede di quelle formulazioni e non può contenere testi approssimati.
|
| Mancano per questo motivo i consensi immagini (`image_disclosure`): nei
| PDF non erano un consenso solo ma almeno due distinti — singolo evento e
| una tantum — con liberatoria ex art. 10 c.c. e art. 96 L. 633/1941. Vanno
| ripresi dai PDF precedenti alla revisione, conservati in
| "Claude outputs\consensi-sostituiti-20260924", e probabilmente serviranno
| più consent_type invece di uno.
|
*/

return [

    'privacy_notice' => 'Dichiaro di aver letto l’informativa sul primo contatto e sull’inserimento dei miei dati nel CRM.',

    'promotional_emails' => 'Acconsento a ricevere tramite newsletter comunicazioni commerciali, offerte e inviti di Welfare Nest.',

];
