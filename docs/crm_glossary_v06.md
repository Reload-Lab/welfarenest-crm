# CRM Glossary — Versione 0.6

## Convenzioni

- Database e codice: inglese
- Interfaccia utente: italiano
- Le chiavi tecniche sono stabili e non dipendono dalla traduzione UI

---

# 1. ENTITÀ PRINCIPALI

## Organization
**UI:** Organizzazione  
Soggetto amministrativo (azienda, ente, fondo, professionista, privato).  
Utilizzato per fatturazione, contratti e relazioni economiche.

---

## Person
**UI:** Persona  
Individuo.  
Non contiene dati fiscali o amministrativi.

---

## Person Organization Relation
**UI:** Relazione persona-organizzazione  
Associazione tra una persona e una organizzazione.  
Descrive ruolo, area e periodo.

---

## Lead
**UI:** Lead  
Contatto non ancora qualificato o convertito in anagrafica.

---

# 2. RECAPITI E INDIRIZZI

## Contact Point
**UI:** Recapito  
Elemento di contatto (email, telefono, social, sito).  
È polimorfico e può appartenere a:
- organization
- person
- person_organization_relation
- lead

---

## Address
**UI:** Indirizzo  
Indirizzo fisico.  
È polimorfico e può appartenere a:
- organization
- person
- person_organization_relation

---

# 3. MODELLO CONTATTI

## Contact Type
**UI:** Tipo contatto  
Tipo generale del recapito.

Esempi:
- email
- phone
- mobile
- website
- social

---

## Contact Channel
**UI:** Canale contatto  
Canale specifico del recapito.

Esempi:
- linkedin
- facebook
- instagram

---

## Contact Usage
**UI:** Uso contatto  
Contesto di utilizzo del recapito.

Esempi:
- personal (personale)
- work (lavoro)
- administrative (amministrativo)
- office (ufficio)
- direct (diretto)
- support (supporto)

---

# 4. STRUTTURA ORGANIZZATIVA

## Organization Type
**UI:** Tipo organizzazione  
Classificazione dell’organizzazione.

Esempi:
- fondo pensione
- banca
- sgr

---

## Organization Role
**UI:** Ruolo organizzazione  
Ruolo dell’organizzazione nel CRM.

Esempi:
- cliente
- fornitore
- partner

---

## Qualification
**UI:** Qualifica  
Ruolo della persona all’interno della relazione.

Esempi:
- direttore
- consulente
- membro CDA

---

## Department
**UI:** Dipartimento / Area  
Area organizzativa della relazione.

---

# 5. CONSENSI

## Consent Type
**UI:** Tipo consenso  
Categoria del consenso.

Esempi:
- privacy_base
- marketing

---

## Consent Version
**UI:** Versione consenso  
Versione specifica del testo del consenso accettato.

Permette la tracciabilità legale.

---

## Consent
**UI:** Consenso  
Evento di consenso (dato, negato, revocato).

Può essere associato a:
- person
- lead
- contact_point

---

# 6. NOTE

## Note
**UI:** Nota  
Annotazione cronologica associata a una entità.

Caratteristiche:
- multiple
- con autore
- con data

---

# 7. CUSTOM FIELDS

## Custom Field
**UI:** Campo personalizzato  
Campo dinamico configurabile dagli amministratori.

---

## Custom Field Option
**UI:** Opzione campo  
Valore selezionabile per campi di tipo select o multiselect.

---

## Custom Field Value
**UI:** Valore campo personalizzato  
Valore assegnato a una entità.

---

# 8. LOGGING

## Audit Log
**UI:** Log modifiche  
Traccia le modifiche ai dati.

---

## Activity Log
**UI:** Log attività  
Traccia l’uso del sistema.

---

## Access Log
**UI:** Log accessi  
Traccia login e accessi al sistema.

---

# 9. PRINCIPI CHIAVE

- Organizations rappresentano soggetti amministrativi
- People rappresentano individui
- Tutti i recapiti sono gestiti tramite contact_points
- Gli indirizzi sono gestiti tramite addresses (polimorfici)
- I consensi sono entità autonome, non booleani
- Le note sono entità separate e cronologiche
- I custom fields servono per estensioni dinamiche, non per dati strutturali