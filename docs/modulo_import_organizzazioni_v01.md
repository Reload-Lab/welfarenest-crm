# Modulo di importazione organizzazioni — v0.1

> Stato al 15/09/2026. Il modulo copre il caricamento iniziale e i caricamenti
> massivi successivi delle **organizzazioni** con i relativi indirizzi e
> recapiti. Non riguarda i lead, non ancora sviluppati, né le persone, previste
> come fase 2.

## 1. Ambito e regola di fondo

L'Excel serve a **inserire organizzazioni nuove**, complete dei loro indirizzi e
recapiti. Tutte le modifiche alle organizzazioni esistenti si fanno dal CRM, mai
dal file.

Conseguenza diretta: l'importazione esegue solo `create` o `skip`. **L'UPDATE non
è un caso d'uso.** Se un'organizzazione risulta già presente, la riga viene
saltata insieme a tutti i suoi figli.

Non è stata adottata una colonna `crm_id` nel tracciato: senza un flusso di
esportazione-modifica-reimportazione resterebbe sempre vuota, e una colonna
sempre vuota prima o poi viene compilata a sproposito.

## 2. Comandi

php artisan import:analyze <file.xlsx> # analisi, nessuna scrittura
php artisan import:run <file.xlsx> [--exclude=riga] [--force]
php artisan import:rollback [batch] [--force]


I percorsi relativi si risolvono rispetto a `storage/app`.

`import:analyze` è il cuore del modulo: legge, valida, collega i tre fogli,
confronta con l'archivio e propone un esito per ogni blocco. `import:run` riusa
la stessa analisi e scrive in una transazione unica.

## 3. Architettura

| Classe | Ruolo |
|---|---|
| `App\Support\ItalianProvinces` | 107 sigle, alias delle soppresse (OT, OG, VS, CI, FO, PS), derivazione della regione |
| `Services\Import\OrganizationWorkbookReader` | Lettura xlsx, controllo strutturale, estrazione righe e vocabolario |
| `Services\Import\ImportStructureException` | File non elaborabile: foglio assente, colonna mancante |
| `Services\Import\FieldNormalizer` | Regole di formato, nessuna dipendenza dal database |
| `Services\Import\NormalizedValue` | Esito di normalizzazione: valore, errore, avviso |
| `Services\Import\LookupResolver` | Risoluzione delle anagrafiche; singleton |
| `Services\Import\ImportRowValidator` | Validazione di riga: payload normalizzato + problemi |
| `Services\Import\OrganizationNameKey` | Chiave di confronto delle denominazioni |
| `Services\Import\ExistingOrganizationIndex` | Indice delle organizzazioni già in archivio |
| `Services\Import\OrganizationImportAnalyzer` | Controlli fra righe, blocchi, deduplica, esiti |
| `Services\Import\ImportExecutor` | Scrittura in transazione, tracciamento |
| `Services\Import\ImportRollback` | Annullamento di un batch |

Dipendenza aggiunta: `phpoffice/phpspreadsheet` ^5.9.

`LookupResolver` è registrato come singleton in `AppServiceProvider`: validatore
e analizzatore devono condividere la stessa istanza, altrimenti il vocabolario
caricato su una non è visto dall'altra.

## 4. Tracciato del file

Tre fogli dati — `Organizzazioni`, `Indirizzi`, `Recapiti` — più cinque fogli
`Fonte_*` che contengono le coppie `code`/`name` delle anagrafiche.

Intestazioni alla riga 1, didascalie alle righe 2 e 3, dati dalla riga 4. Le
colonne si mappano **per nome, non per posizione**: spostarle non rompe il file,
e una colonna in più produce un avviso, non un errore.

Indirizzi e recapiti si collegano all'organizzazione tramite la colonna
`Organizzazione` (il nome), non tramite `Codice_Organizzazione`, che nel template
è una formula e può arrivare senza valore in cache. Il codice, quando presente,
viene usato come controllo incrociato: se non corrisponde, avviso.

## 5. Catena di risoluzione delle anagrafiche

cella "Istituzione" → foglio Fonte_ del file → code "istituzione" → archivio → id


I fogli `Fonte_` non sono decorativi: sono il **vocabolario congelato** al momento
in cui il file è stato generato. Se qualcuno rinomina una tipologia dalla gestione
anagrafiche, i file già compilati continuano a importare correttamente, perché il
ponte è il `code`, non l'etichetta.

Se il file dichiara un code che in archivio non esiste **non si ripiega sul
nome**: sarebbe il modo per agganciare in silenzio la voce sbagliata. Si produce
un errore che nomina il codice mancante.

Se i fogli `Fonte_` mancano, si ricade sul confronto per nome normalizzato
(minuscole, accenti rimossi, punteggiatura come separatore), con avviso.

Una voce disattivata (`is_active = false`) non compare nei menu a tendina ma
**viene accettata in importazione** con un avviso: un file compilato mesi prima
non deve diventare inutilizzabile perché nel frattempo una voce è stata ritirata.

## 6. Validazione

Quattro livelli, due severità.

- **Struttura** — fogli presenti, intestazioni conformi. Fallimento: il file non è elaborabile.
- **Formato** — partita IVA (11 cifre + codice di controllo), codice fiscale, CAP, sigla provincia, telefono, email, URL, SDI.
- **Anagrafiche** — i valori esistono nel database, non in una lista scritta nel codice.
- **Regole di dominio** — almeno uno fra `name` e `legal_name`; un solo primario per organizzazione e tipo; nessun recapito ripetuto sulla stessa organizzazione; codici e denominazioni univoci nel file.

**Errore** rende la riga non importabile. **Avviso** la lascia passare e la segnala.

### 6.1 Ripulitura dei caratteri invisibili

`FieldNormalizer::clean()` elimina i caratteri a larghezza zero e riconduce gli
spazi Unicode a spazio normale, **su ogni campo, prima di qualunque controllo**.

Non è un dettaglio: nel file reale un NBSP in coda a una ragione sociale non
produceva alcun errore e sarebbe finito in archivio, rompendo per sempre ogni
confronto esatto su quella denominazione — cioè la deduplica.

## 7. Deduplica

Gerarchia, tutta a **confronto esatto** su forma normalizzata:

1. `vat_number` uguale
2. `tax_code` uguale
3. denominazione normalizzata, **confrontata in croce**: `name` e `legal_name` del file contro `name` e `legal_name` di ogni record esistente

Il confronto incrociato è necessario perché i due campi spesso non si somigliano
("Agenas" / "Agenzia Nazionale per i Servizi Sanitari Regionali") e un file
successivo potrebbe portarne solo uno.

`OrganizationNameKey` rimuove ogni carattere non alfanumerico, **spazi compresi**,
così "Aon Spa" e "AON S.p.A." coincidono. Non rimuove le forme societarie:
toglierle farebbe collassare ragioni sociali diverse dello stesso gruppo.

### 7.1 Perché non c'è similarità testuale

Il dominio è composto in larga parte da fondi sanitari con nomi-acronimo distanti
uno o due caratteri, che sono enti diversi: ASSIDA/ASSIDAI/ASSIDIM,
FASI/FASIE/FASIF, FASDA/FASDAC/FASDAPI, Fondo FASA/Fondo FAST.

Sul file reale una soglia di similarità a 0.82 produce 17 coppie sospette, **tutte
false**, a fronte di zero duplicati veri. Alert infondati alla prima importazione
addestrano l'operatore a ignorarli: la similarità carattere-per-carattere è stata
quindi esclusa dal modulo.

## 8. Il blocco atomico

Organizzazione, suoi indirizzi e suoi recapiti sono un'unità. Se un figlio ha un
errore bloccante, l'esito proposto per l'intero blocco è l'esclusione.

La ragione: se l'organizzazione venisse creata senza quell'indirizzo, al
caricamento successivo risulterebbe già presente e l'indirizzo verrebbe saltato
di nuovo. **Le lacune di una prima passata non si recuperano più da Excel**:
andrebbero inserite a mano nel CRM.

Verificato sul file reale: quattro organizzazioni valide sono state escluse per un
problema di un figlio. È il comportamento voluto, ed è ciò che giustifica la
forzatura per singolo blocco prevista nell'anteprima web.

Quando un'organizzazione risulta già presente, l'anteprima mostra **quali campi
del file differiscono da quelli nel CRM**, senza scrivere nulla: l'importazione
diventa anche un rilevatore di divergenze, lasciando il CRM unico luogo di
scrittura.

## 9. Principio guida

> **L'incompletezza è importabile, la scorrettezza no.**

Un dato mancante entra con un avviso, eventualmente con un ripiego dichiarato. Un
dato *sbagliato* blocca, perché importarlo significherebbe scrivere nel CRM
qualcosa di falso.

Applicazioni:

- `organization_type` vuoto → tipologia `da_classificare`, con avviso
- `organization_type` scritto male → errore, nessun ripiego
- CAP o civico mancanti → avviso; CAP malformato → errore
- `sdi_code` con 11 cifre → errore, con il messaggio che suggerisce la partita IVA nella colonna sbagliata

### 9.1 La tipologia "Da classificare"

`organizations.organization_type_id` è NOT NULL con foreign key, e il tipo è la
classificazione su cui poggiano elenchi e filtri: renderlo nullable avrebbe
richiesto di rivedere ogni vista che legge `$organization->organizationType->name`.

È stata invece introdotta la tipologia `da_classificare`, seedata **disattivata**:
non compare nei menu a tendina, la assegna solo l'importazione, e il filtro sul
tipo diventa la lista delle organizzazioni da classificare a mano.

## 10. Scrittura e tracciamento

`ImportExecutor` scrive esclusivamente tramite i model, **mai passando dai
controller**: `ContactPointController` genera automaticamente una richiesta di
consenso per i recapiti email delle persone, e in fase 2 un'importazione massiva
ne spedirebbe un centinaio.

Tutto avviene in una `DB::transaction` unica: se qualcosa fallisce non resta né un
record parziale né il batch.

Due tabelle di tracciamento, nessuna colonna aggiunta a quelle di dominio:

- `import_batches` — chi, quando, quale file, impronta SHA-256, conteggi
- `import_row_results` — quale riga del file ha prodotto quale record

L'impronta del file intercetta il ricarico dello stesso identico file e chiede
conferma con `--force`. Non è la protezione contro i duplicati — quella è la
deduplica — è un avviso per chi sta ripetendo un'operazione senza accorgersene.

### 10.1 Rollback

`import:rollback` usa il tracciamento per cancellare ciò che un batch ha creato.

Attenzione: indirizzi, recapiti, note e consensi sono collegati tramite
`owner_type`/`owner_id` **senza foreign key**, quindi cancellare l'organizzazione
non li porta via. Il comando li gestisce esplicitamente e si ferma se rileva righe
aggiunte dal CRM dopo l'importazione, che resterebbero orfane.

Assegnazioni di ruolo, relazioni con le persone e account WN Plus hanno invece
foreign key in cascata e spariscono da soli.

## 11. Modifiche allo schema

- `addresses.province`, `addresses.region` → nullable
- `addresses.postal_code`, `addresses.street_number` → nullable

Regola adottata: **indirizzo utile = via + città**. Restano obbligatori
`address_type`, `street`, `city`; `region` è derivata dalla sigla provincia;
`country` ha valore predefinito "Italia".

I cinque seeder delle anagrafiche sono stati riscritti con `code` **espliciti**
(mai derivati dal `name`) e in modalità `insertOrIgnore`: le anagrafiche hanno
un'interfaccia di gestione nell'applicazione, quindi sono dati modificabili a
runtime, e un seeder che li sovrascrive cancellerebbe le modifiche fatte a mano.

> Correzione a `crm_data_model_v08.md` §2: esiste un'interfaccia di gestione delle
> tipologie organizzazione e delle altre anagrafiche. L'affermazione "gestita solo
> via seeder" è superata.

## 12. Difetti dei dati riscontrati sul file reale

Su 275 organizzazioni, 67 indirizzi e 72 recapiti:

- **caratteri invisibili** — NBSP in `vat_number` e in coda a un `legal_name`
- **righe fantasma** — sette righe con il solo `is_primary` valorizzato, residuo di un trascinamento. Regola adottata: una riga con sole caselle di spunta è una riga vuota, segnalata con un avviso che ne elenca i numeri
- **colonna sbagliata** — una partita IVA inserita in `sdi_code`
- **`legal_name` troncati** a 32 caratteri in due righe: da verificare alla fonte, perché un nome mozzato non matcherà mai più in modo esatto
- **18 organizzazioni senza tipologia**, già note e non determinabili automaticamente

Esito finale: 275 organizzazioni importate, 67 indirizzi, 72 recapiti, 39 avvisi.

## 13. Cosa resta

- **Schermata web di anteprima** — lettura dello stesso risultato con le caselle per escludere i blocchi e i menu per assegnare le tipologie "Da classificare" prima di confermare. Richiede una terza tabella, `import_rows`, per riprendere l'anteprima in una sessione successiva
- **Generatore del template** dal database: fogli `Fonte_` sempre allineati, menu a tendina con le sole voci attive, nessuna riga di esempio, foglio nascosto con data di generazione e versione del tracciato. Il tracciato delle colonne va spostato da `OrganizationWorkbookReader` a `config/crm_import.php`, condiviso fra lettore e generatore
- **Foglio Persone** (fase 2): richiede `person_organization_relations` con qualifica e reparto, e una deduplica diversa — sulle persone l'omonimia è la norma, quindi la chiave non può essere nome e cognome
