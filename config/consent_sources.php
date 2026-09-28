<?php

/*
|--------------------------------------------------------------------------
| Origini di un consenso
|--------------------------------------------------------------------------
|
| Elenco controllato dei valori ammessi per `consents.source`, con l'etichetta
| mostrata nel registro e il flag `manual`.
|
| `manual` distingue i consensi raccolti da un flusso digitale (il destinatario
| ha spuntato una casella di persona) da quelli digitati da un operatore del
| CRM sulla base di una prova esterna. È la distinzione che dà valore probatorio
| al registro e va tenuta esplicita: un consenso manuale è una dichiarazione di
| chi lo inserisce, non l'atto del titolare dei dati.
|
| I primi tre valori erano già scritti a database prima che questo elenco
| esistesse: non vanno rinominati, o lo storico diventa illeggibile.
|
*/

return [

    'email_consent_request' => [
        'label' => 'Link pubblico consensi',
        'manual' => false,
    ],

    'wn_plus_onboarding' => [
        'label' => 'Onboarding WN+',
        'manual' => false,
    ],

    'wn_plus_portal' => [
        'label' => 'Portale WN+',
        'manual' => false,
    ],

    'manual_email' => [
        'label' => 'Inserito a mano — email',
        'manual' => true,
    ],

    'manual_message' => [
        'label' => 'Inserito a mano — messaggio',
        'manual' => true,
    ],

    'manual_paper' => [
        'label' => 'Inserito a mano — modulo cartaceo',
        'manual' => true,
    ],

    'manual_other' => [
        'label' => 'Inserito a mano — altro',
        'manual' => true,
    ],

];
