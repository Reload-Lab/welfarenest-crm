# Piano completo per collegare Organization e People

## Obiettivo

Completare il collegamento bidirezionale tra `Organization` e `People` usando la tabella ponte già esistente `person_organization_relations`, così da rendere la relazione gestibile e visibile sia dalla scheda persona sia dalla scheda organizzazione, senza introdurre nuove entità o duplicazioni di dati.

## Stato attuale

- Il modello dati di base esiste già:
  - `people`
  - `organizations`
  - `person_organization_relations`
  - `qualifications`
  - `departments`
- La gestione delle relazioni è già attiva nella scheda persona:
  - creazione relazione
  - modifica relazione
  - visualizzazione elenco relazioni
- La scheda organizzazione contiene ancora un placeholder nella sezione persone.
- Il controller delle organizzazioni non carica ancora le relazioni con le persone.
- Non esiste ancora un flusso dedicato per aggiungere o modificare una relazione partendo da una organizzazione.
- Le regole di dominio già note sono coerenti con il modello attuale:
  - una persona può esistere senza organizzazioni
  - una persona può avere più relazioni con la stessa organizzazione
  - `end_date` non può precedere `start_date`
  - `is_active` è indipendente dalle date

## Risultato atteso

Alla fine delle modifiche:

- la scheda persona continuerà a mostrare e gestire le relazioni con organizzazioni;
- la scheda organizzazione mostrerà l’elenco completo delle persone collegate;
- dalla scheda organizzazione sarà possibile inserire e modificare una relazione verso una persona;
- il caricamento dati sarà coerente e senza N+1;
- la UI userà sempre terminologia business:
  - `Persone`
  - `Persona`
  - `Organizzazione`
  - `Relazioni`

## Piano delle modifiche per file

### 1. `app/Models/Organization.php`

**Modifiche**

- Confermare e mantenere la relazione `hasMany` verso `PersonOrganizationRelation`.
- Aggiungere una relazione derivata e più leggibile per accedere alle persone collegate tramite la tabella ponte.
- Definire chiaramente il criterio con cui distinguere:
  - relazioni complete
  - persone collegate

**Motivazione**

Oggi il modello organizzazione conosce solo la tabella ponte. Per costruire una scheda organizzazione davvero usabile serve un accesso chiaro sia alle relazioni complete sia, quando utile, alle persone associate. Questo evita logica dispersa nei controller o nelle view.

### 2. `app/Models/Person.php`

**Modifiche**

- Confermare la relazione `hasMany` già presente verso `PersonOrganizationRelation`.
- Valutare l’aggiunta di una relazione derivata verso le organizzazioni collegate, distinta dalla relazione tecnica verso la tabella ponte.
- Mantenere `full_name` come attributo di presentazione principale.

**Motivazione**

La scheda persona funziona già, ma una relazione derivata più esplicita rende il modello simmetrico rispetto a `Organization` e semplifica i futuri usi applicativi senza alterare il comportamento attuale.

### 3. `app/Models/PersonOrganizationRelation.php`

**Modifiche**

- Mantenere il modello come entità centrale della relazione.
- Verificare che il modello rappresenti in modo completo tutti gli attributi di business della connessione:
  - persona
  - organizzazione
  - qualifica
  - dipartimento
  - periodo
  - stato
  - indicazione di relazione principale
- Valutare l’introduzione di criteri di ordinamento applicativo coerenti per la visualizzazione:
  - prima relazioni attive
  - poi relazioni principali
  - poi per data o nome

**Motivazione**

Questa è l’entità che contiene il valore reale della connessione tra persone e organizzazioni. Il piano deve consolidarla come punto unico della relazione, evitando che campi di relazione finiscano impropriamente su `people` o `organizations`.

### 4. `app/Http/Controllers/OrganizationController.php`

**Modifiche**

- Estendere il metodo `show()` per caricare:
  - relazioni con persone
  - persona associata
  - qualifica
  - dipartimento
- Caricare anche i dataset necessari al form se dalla scheda organizzazione si vuole creare o modificare una relazione:
  - elenco persone
  - qualifiche attive
  - dipartimenti attivi
- Introdurre un ordinamento leggibile delle relazioni nella scheda organizzazione.
- Valutare se mostrare relazioni attive e non attive nello stesso elenco o con separazione visiva.

**Motivazione**

Il blocco persone nella scheda organizzazione oggi non è operativo perché il controller non prepara i dati necessari. Questo è il punto principale da completare per attivare il lato `Organization -> People`.

### 5. `app/Http/Controllers/PersonController.php`

**Modifiche**

- Mantenere il caricamento delle relazioni nella scheda persona.
- Uniformare, se necessario, ordinamento e presentazione delle relazioni rispetto a quanto sarà fatto nella scheda organizzazione.
- Valutare se mostrare in modo più esplicito la relazione principale o la relazione attiva corrente.

**Motivazione**

Il collegamento deve essere coerente nei due sensi. Se il lato organizzazione viene migliorato, anche la scheda persona deve restare allineata sul piano di ordinamento, etichette e leggibilità.

### 6. `app/Http/Controllers/PersonOrganizationRelationController.php`

**Modifiche**

- Mantenere i metodi `store()` e `update()` come punto unico di salvataggio della relazione.
- Estendere il flusso di redirect per supportare anche il caso di origine dalla scheda organizzazione, non solo dalla scheda persona.
- Prevedere un meccanismo chiaro per distinguere il contesto di provenienza:
  - ritorno alla persona
  - ritorno all’organizzazione
- Verificare le validazioni già presenti e completarle sul piano funzionale:
  - coerenza date
  - vincoli booleani
  - corretto abbinamento della relazione alla persona
- Valutare la gestione applicativa del flag `is_primary` per evitare incoerenze tra relazioni della stessa persona.

**Motivazione**

Oggi il controller è progettato di fatto per la sola scheda persona. Se la relazione deve essere gestibile anche dalla scheda organizzazione, il salvataggio deve diventare neutro rispetto al punto di ingresso, mantenendo un solo flusso di persistenza.

### 7. `routes/web.php`

**Modifiche**

- Razionalizzare le rotte già esistenti evitando duplicazioni della sezione `organizations`.
- Mantenere le rotte resource per `people` e `organizations`.
- Confermare o estendere le rotte per `people/{person}/relations` in modo che possano supportare anche l’uso dalla scheda organizzazione.
- Valutare se introdurre rotte più esplicite per le relazioni dal lato organizzazione oppure se riusare l’attuale controller con parametri di contesto.

**Motivazione**

Le rotte attuali mostrano già un punto di attenzione: la sezione `organizations` è dichiarata sia come resource sia con singole rotte ripetute. Prima di estendere il collegamento bidirezionale conviene chiarire questo livello per evitare collisioni, ridondanze e manutenzione difficile.

### 8. `resources/views/organizations/show.blade.php`

**Modifiche**

- Mantenere la sezione persone come parte stabile della scheda organizzazione.
- Sostituire il placeholder attuale con una vista realmente alimentata dai dati caricati dal controller.
- Verificare che il layout resti coerente con la pagina organizzazione esistente.

**Motivazione**

Questa è la pagina finale in cui l’utente deve percepire il collegamento tra organizzazione e persone. Oggi la struttura c’è già, ma non è ancora connessa al modello dati.

### 9. `resources/views/organizations/partials/show/people.blade.php`

**Modifiche**

- Sostituire il contenuto placeholder con un elenco reale delle relazioni persona-organizzazione.
- Mostrare per ogni riga almeno:
  - persona
  - qualifica
  - dipartimento
  - periodo
  - stato
  - indicatore principale
  - azioni
- Rendere cliccabile la persona per aprire la relativa scheda.
- Introdurre il pulsante per aggiungere una nuova relazione dalla scheda organizzazione.
- Prevedere lo stato vuoto con messaggio coerente quando non esistono persone collegate.
- Prevedere una modalità di modifica della singola relazione dalla stessa scheda.

**Motivazione**

Questo partial è il punto UI più importante del lavoro: oggi dichiara chiaramente l’intento del modulo ma non implementa nessuna funzionalità. Qui si materializza davvero il collegamento `Organization -> People`.

### 10. Nuovo partial vista per il form relazione lato organizzazione

**Modifiche**

- Introdurre un partial dedicato, oppure rendere riusabile quello già esistente, per compilare una relazione partendo da una organizzazione.
- Il form dovrà permettere di scegliere:
  - persona
  - qualifica
  - dipartimento
  - data inizio
  - data fine
  - stato
  - relazione principale
- Valutare se convenga avere:
  - un form condiviso tra persona e organizzazione con parametri di contesto
  - due partial distinti ma coerenti

**Motivazione**

Il form attuale è pensato per una persona già nota e lascia selezionare l’organizzazione. Dalla scheda organizzazione serve il flusso opposto: organizzazione già nota e persona da selezionare. Questo richiede un adattamento esplicito di struttura e naming.

### 11. `resources/views/people/partials/show/relation-form.blade.php`

**Modifiche**

- Valutare la trasformazione del partial in componente riusabile anche dal lato organizzazione.
- Separare chiaramente i campi fissi dal contesto di origine:
  - da scheda persona: persona nota, organizzazione selezionabile
  - da scheda organizzazione: organizzazione nota, persona selezionabile
- Uniformare etichette, validazioni visuali e comportamento del modal.

**Motivazione**

Se il form resta monodirezionale, il progetto rischia duplicazione di markup e regole. Questo file è il candidato naturale per diventare la base di una soluzione condivisa.

### 12. `resources/views/people/partials/show/relations.blade.php`

**Modifiche**

- Mantenere l’attuale tabella relazioni.
- Allineare la struttura tabellare al nuovo blocco persone della scheda organizzazione.
- Uniformare badge, ordine colonne e stato dei record.
- Valutare se aggiungere link diretto alla scheda organizzazione nelle righe già presenti.

**Motivazione**

Il collegamento deve funzionare in entrambi i sensi con la stessa grammatica visiva. Questo partial è già maturo e può diventare la base di riferimento per il lato organizzazione.

### 13. `resources/views/people/show.blade.php`

**Modifiche**

- Verificare che la scheda persona continui a restare completa dopo l’introduzione della simmetria con le organizzazioni.
- Valutare piccoli aggiustamenti di navigazione reciproca tra scheda persona e scheda organizzazione.

**Motivazione**

Non serve riscriverla, ma va mantenuta coerente con il nuovo comportamento generale del modulo relazioni.

### 14. `resources/views/people/index.blade.php`

**Modifiche**

- Valutare se mantenere solo il conteggio relazioni oppure arricchirlo in futuro con informazione più significativa:
  - relazione attiva
  - organizzazione principale
- Per questa fase è sufficiente verificare la coerenza del conteggio con eventuali nuovi criteri di visualizzazione.

**Motivazione**

L’indice persone è già connesso alle relazioni tramite `withCount`. Non è il cuore del lavoro, ma va verificato per evitare disallineamenti semantici.

### 15. `resources/views/organizations/index.blade.php`

**Modifiche**

- Valutare se aggiungere in una fase successiva un indicatore sintetico del numero di persone collegate.
- Per questa fase non è strettamente obbligatorio modificarlo, ma va considerato nel piano di coerenza complessiva.

**Motivazione**

Il collegamento `Organization -> People` diventa più utile se, almeno in prospettiva, è visibile anche nell’elenco organizzazioni. Non è indispensabile per il primo rilascio ma è una naturale estensione del lavoro.

### 16. `database/migrations/2026_04_01_123000_create_person_organization_relations_table.php`

**Modifiche**

- Verificare che la struttura attuale sia sufficiente per il caso d’uso.
- Valutare l’aggiunta di un vincolo logico o tecnico per contenere casi incoerenti sul flag `is_primary`.
- Valutare la presenza di indici composti utili per i carichi principali:
  - per persona
  - per organizzazione
  - per stato
  - per ordinamento temporale

**Motivazione**

La tabella esiste già e copre i campi fondamentali. Il piano deve però considerare se i vincoli e gli indici sono adeguati a una consultazione bidirezionale più frequente.

### 17. `database/migrations/2026_04_08_140000_add_dates_to_person_organization_relations_table.php`

**Modifiche**

- Confermare l’adeguatezza di `start_date` e `end_date` rispetto ai filtri e agli ordinamenti che saranno usati nelle pagine.
- Valutare se gli indici singoli sulle date siano sufficienti o se debbano essere accompagnati da indici più utili al recupero applicativo.

**Motivazione**

Le date sono già presenti ma il loro valore aumenta quando il modulo viene consultato da entrambe le schede. Il piano deve prevedere eventuali ottimizzazioni, non solo il riuso dei campi.

### 18. `database/seeders/QualificationSeeder.php` e `database/seeders/DepartmentSeeder.php`

**Modifiche**

- Verificare che i dati seed garantiscano opzioni utilizzabili nei form relazione.
- Assicurarsi che la UI non dipenda da seed incompleti o disallineati.

**Motivazione**

Poiché qualifiche e dipartimenti entrano direttamente nella relazione, il modulo è credibile solo se queste lookup risultano stabili e disponibili.

### 19. Documentazione in `docs/`

**Modifiche**

- Aggiornare la documentazione funzionale solo se, durante l’implementazione, emergono scelte applicative che devono essere rese esplicite.
- In particolare, documentare eventuali regole aggiuntive su:
  - relazione principale
  - ordinamento predefinito
  - gestione delle relazioni storiche
  - uso bidirezionale delle schede

**Motivazione**

Il modello di dominio è già descritto, ma l’implementazione del collegamento bidirezionale può introdurre decisioni operative che conviene fissare per evitare ambiguità future.

## Decisioni funzionali da chiarire durante l’implementazione

### A. Significato di `is_primary`

Da definire con precisione se:

- una persona può avere una sola relazione principale globale;
- una persona può avere una relazione principale per organizzazione;
- una organizzazione può avere più persone marcate come principali;
- il flag è solo informativo e non deve essere vincolato.

**Impatto**

Questa decisione incide su validazioni, comportamento del controller e possibili vincoli DB.

### B. Ordinamento predefinito delle relazioni

Da stabilire un criterio uniforme per entrambe le schede, ad esempio:

- attive prima delle inattive;
- principali prima delle non principali;
- relazioni correnti prima di quelle storiche;
- ordinamento alfabetico per persona o organizzazione a parità di stato.

**Impatto**

Serve a mantenere la UX coerente e prevedibile.

### C. Gestione delle relazioni storiche

Da chiarire se:

- le relazioni chiuse devono restare sempre visibili;
- le relazioni non attive devono apparire nello stesso elenco o in sezione separata;
- i filtri futuri dovranno distinguere relazioni correnti e storiche.

**Impatto**

Incide su query, layout e leggibilità delle schede.

### D. Punto di ingresso per la creazione relazione

Da decidere se il primo rilascio deve supportare:

- sola visualizzazione lato organizzazione;
- visualizzazione più modifica;
- visualizzazione più creazione più modifica complete dalla scheda organizzazione.

**Impatto**

Questa decisione cambia l’ampiezza reale del lavoro UI e controller.

## Piano di implementazione consigliato

### Fase 1. Consolidamento dominio e query

- Allineare i modelli.
- Consolidare le relazioni Eloquent necessarie.
- Definire il criterio di ordinamento delle relazioni.
- Chiarire il comportamento di `is_primary`.

### Fase 2. Attivazione lato organizzazione

- Estendere `OrganizationController@show`.
- Preparare i dataset necessari al blocco persone.
- Sostituire il placeholder della scheda organizzazione con elenco reale.

### Fase 3. Riutilizzo o rifattorizzazione del form relazione

- Rendere riusabile il form attuale oppure creare un partial parallelo per il contesto organizzazione.
- Gestire correttamente il redirect post-salvataggio in base alla pagina di origine.

### Fase 4. Coerenza UI bidirezionale

- Allineare tabella, badge, colonne e azioni tra scheda persona e scheda organizzazione.
- Verificare la navigazione reciproca tra i due moduli.

### Fase 5. Rifinitura tecnica

- Ripulire le rotte duplicate.
- Valutare indici o vincoli aggiuntivi.
- Aggiornare la documentazione se emergono regole applicative definitive.

## Priorità pratica delle modifiche

### Priorità alta

- `app/Http/Controllers/OrganizationController.php`
- `resources/views/organizations/partials/show/people.blade.php`
- form relazione riusabile o equivalente lato organizzazione
- `app/Http/Controllers/PersonOrganizationRelationController.php`
- `routes/web.php`

### Priorità media

- `app/Models/Organization.php`
- `app/Models/Person.php`
- `resources/views/people/partials/show/relations.blade.php`
- `resources/views/people/show.blade.php`

### Priorità bassa ma da valutare

- ottimizzazione indici su `person_organization_relations`
- arricchimenti degli index `people` e `organizations`
- aggiornamenti documentali aggiuntivi

## Rischi da tenere sotto controllo

- Duplicazione della logica del form relazione tra lato persona e lato organizzazione.
- Redirect incoerenti dopo salvataggio o modifica della relazione.
- Ambiguità sul significato del flag `is_primary`.
- Rotte duplicate o conflittuali nella configurazione attuale.
- Differenze di ordinamento o semantica tra scheda persona e scheda organizzazione.
- Possibili N+1 se il caricamento lato organizzazione non usa eager loading completo.

## Conclusione

Il progetto possiede già la base dati corretta e una prima implementazione funzionante dal lato persona. Il lavoro necessario per collegare davvero `Organization` e `People` consiste soprattutto nel completare il lato organizzazione, riusare correttamente la tabella `person_organization_relations` come entità centrale, rendere il form relazione utilizzabile in entrambi i contesti e ripulire rotte e regole di navigazione per ottenere un modulo coerente, bidirezionale e manutenibile.
