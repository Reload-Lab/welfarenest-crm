# CRM Business Rules — Versione 0.6

## 1. PRINCIPI GENERALI

- Il modello dati è definito nel documento `crm_data_model_v06_consolidated.md`
- Le definizioni terminologiche sono nel glossario
- Le regole qui descritte guidano:
  - validazioni
  - comportamenti applicativi
  - vincoli logici

---

# 2. ORGANIZATIONS

- Almeno uno tra `name` e `legal_name` deve essere valorizzato
- `organization_type_id` deve riferirsi a un tipo attivo
- Una organization può avere più ruoli (cliente, fornitore)
- `vat_number` e `tax_code` non sono obbligatori globalmente, ma possono diventarlo in contesti amministrativi

---

# 3. PEOPLE

- `first_name` e `last_name` rappresentano il minimo per creare una persona
- Una persona può esistere senza relazioni
- Una persona non contiene dati fiscali

---

# 4. PERSON_ORGANIZATION_RELATIONS

- Una relazione collega una persona a una organization
- Una persona può avere più relazioni con la stessa organization (storico o ruoli diversi)
- `end_date` non può essere precedente a `start_date`
- `is_active` può essere gestito indipendentemente dalle date

---

# 5. ADDRESSES

- Ogni address appartiene a una sola entità tramite `owner_type` + `owner_id`
- `owner_type` ammessi:
  - organization
  - person
  - person_organization_relation

- `address_type_id` deve essere valido e attivo

- Regola consigliata:
  - un solo address `is_primary = true` per:
    - owner
    - address_type

---

# 6. CONTACT_POINTS

- Ogni contact_point appartiene a una sola entità
- `owner_type` ammessi:
  - organization
  - person
  - person_organization_relation
  - lead

- `contact_channel_id` deve essere coerente con `contact_type_id`
- `contact_usage_id` è opzionale

- Regola consigliata:
  - un solo contact_point `is_primary = true` per:
    - owner
    - contact_type

- Il campo `value` deve essere validato in base al tipo:
  - email → formato email
  - phone → formato numero

---

# 7. CONSENTS

- Il consenso è un’entità autonoma, non un booleano

- `consent_version_id`, se presente:
  - deve appartenere allo stesso `consent_type_id`

- Tipi principali:
  - `privacy_base` → associato a person o lead
  - `marketing` → associato a contact_point

- Lo stato del consenso:
  - granted
  - denied
  - revoked
  - pending

- Il sistema deve permettere di sapere:
  - quale versione del consenso è stata accettata

---

# 8. NOTES

- Le note sono cronologiche
- Una entità può avere più note
- Le note non sostituiscono attività/task
- Ogni nota dovrebbe avere un autore

---

# 9. CUSTOM FIELDS

- I custom fields non sostituiscono dati strutturali
- Ogni campo è associato a una `entity_type`
- `organization_type_id` può limitare la visibilità

- In `custom_field_values`:
  - deve essere valorizzato solo il campo coerente con `field_type`

- Regola consigliata:
  - un solo valore per:
    - custom_field_id
    - owner_type
    - owner_id

---

# 10. LOGGING

## Audit Logs
- Tracciano modifiche ai dati
- Devono registrare:
  - create
  - update
  - delete

## Activity Logs
- Tracciano uso del sistema
- Non sostituiscono audit logs

## Access Logs
- Tracciano accessi e sicurezza
- Eventi minimi:
  - login riuscito
  - login fallito
  - logout

---

# 11. REGOLE TRASVERSALI

- Naming in inglese
- UI in italiano
- Polimorfismo standardizzato con `owner_type` e `owner_id`
- `owner_type` NON deve contenere classi Laravel
- Uso di valori stringa controllati (organization, person, ecc.)

---

# 12. PRINCIPI DI PROGETTAZIONE

- Separazione tra:
  - dati
  - comportamento
  - tracciamento

- Evitare duplicazioni:
  - recapiti solo in contact_points
  - indirizzi solo in addresses

- Preferire:
  - estendibilità (custom fields)
  - tracciabilità (logs, consents)