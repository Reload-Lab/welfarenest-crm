<?php

use App\Http\Controllers\Api\WnPlusAccountApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API per plus.welfarenest.it
|--------------------------------------------------------------------------
|
| Endpoint server-to-server consumati dal sito WN+ (WordPress). Non sono una
| API pubblica: stanno tutti dietro il middleware wn-plus.api, che richiede il
| token condiviso e, se configurata, l'allowlist degli IP.
|
| Due endpoint distinti e non uno solo, per una ragione di sostanza:
|
|   accounts  espone il quadro completo (email, ruolo, stato di tutti i
|             consensi) e serve a WordPress per creare e aggiornare gli utenti.
|             Non deve mai finire in una pagina.
|
|   directory elenca i soli membri che hanno acconsentito a comparire, con i
|             campi gia' rimossi quando il consenso corrispondente manca. La
|             minimizzazione sta qui, dove i consensi vivono, e non nel tema
|             WordPress: un if sbagliato di la' pubblicherebbe dati che nessuno
|             ha autorizzato.
|
*/

Route::middleware('wn-plus.api')->prefix('wn-plus')->name('api.wn-plus.')->group(function () {
    Route::get('accounts', [WnPlusAccountApiController::class, 'index'])->name('accounts');
    Route::get('directory', [WnPlusAccountApiController::class, 'directory'])->name('directory');
});
