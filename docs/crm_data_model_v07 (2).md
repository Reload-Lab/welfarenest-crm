📘 CRM DATA MODEL — Versione 0.7 (aggiornata)
⚠️ Modifiche rispetto v0.6
❌ rimossa tabella contact_channels
✅ contact_types estesa con category
❌ rimosso contact_channel_id da contact_points
1. ENTITÀ CORE
ORGANIZATIONS
id (PK)
name
legal_name
organization_type_id (FK)
vat_number
tax_code
sdi_code
is_split_payment
is_active
created_at
updated_at
PEOPLE
id (PK)
first_name
last_name
created_at
updated_at
PERSON_ORGANIZATION_RELATIONS
id (PK)
person_id (FK)
organization_id (FK)
qualification_id (FK nullable)
department_id (FK nullable)
start_date
end_date
is_active
created_at
updated_at
LEADS
id (PK)
organization_id (nullable)
person_id (nullable)
lead_status_id (FK)
lead_source_id (nullable FK)
name
description
estimated_value
expected_close_date
assigned_user_id
is_active
created_at
updated_at
2. CLASSIFICAZIONI

Tutte seguono lo stesso schema:

id
code (unique)
name
description (nullable)
is_active
sort_order
created_at
updated_at
Tabelle:
organization_types
organization_roles
qualifications
departments
address_types
contact_types (⚠️ con category)
contact_usages
lead_statuses
lead_sources
consent_types
3. CONTACT MODEL (AGGIORNATO)
CONTACT_TYPES
id
code
name
category ← 🔥 NUOVO
description
is_active
sort_order
created_at
updated_at
Esempi
code	name	category
email	Email	email
pec	PEC	email
phone	Telefono	phone
mobile	Cellulare	phone
linkedin	LinkedIn	social
website	Sito web	web
CONTACT_POINTS
id
owner_type
owner_id
contact_type_id (FK)
contact_usage_id (FK nullable)
value
label
is_primary
is_active
created_at
updated_at
owner_type ammessi
organization
person
person_organization_relation
lead
4. ADDRESSES
ADDRESSES
id
owner_type
owner_id
address_type_id (FK)
label
street
street_number
postal_code
city
province
region
country
is_primary
created_at
updated_at
5. CONSENSI
CONSENT_VERSIONS
id
consent_type_id (FK)
version_code
title
content_text
content_file_path
is_active
published_at
created_at
updated_at
CONSENTS
id
consent_type_id (FK)
consent_version_id (nullable FK)
owner_type
owner_id
status
granted_at
revoked_at
source
created_at
updated_at
6. NOTE
NOTES
id
owner_type
owner_id
author_user_id (FK)
content
note_type
is_pinned
created_at
7. ORGANIZATION ROLES
ORGANIZATION_ROLE_ASSIGNMENTS
id
organization_id (FK)
organization_role_id (FK)
created_at
updated_at
8. LOGGING
AUDIT_LOGS
id
user_id (nullable FK)
event_type
auditable_type
auditable_id
old_values_json
new_values_json
created_at
ACTIVITY_LOGS
id
user_id (nullable FK)
activity_type
subject_type (nullable)
subject_id (nullable)
created_at
ACCESS_LOGS
id
user_id (nullable FK)
event_type
ip_address
user_agent
created_at
9. CUSTOM FIELDS
CUSTOM_FIELDS
id
name
slug
entity_type
field_type
organization_type_id (nullable FK)
is_required
is_active
sort_order
created_at
updated_at
CUSTOM_FIELD_OPTIONS
id
custom_field_id (FK)
value
label
sort_order
is_active
created_at
updated_at
CUSTOM_FIELD_VALUES
id
custom_field_id (FK)
owner_type
owner_id
value_text
value_number
value_date
value_boolean
value_json
created_at
updated_at
🔥 REGOLE TRASVERSALI (CRITICHE)
naming: snake_case
FK: *_id
boolean: is_*
polimorfismo:
owner_type (string controllata)
owner_id
NO classi Laravel nei polimorfici
database in inglese, UI in italiano
recapiti SOLO in contact_points
indirizzi SOLO in addresses
consensi = entità autonome (non boolean)