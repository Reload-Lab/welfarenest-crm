# AGENTS.md

## Project
Internal CRM built with Laravel 12.

## Main stack
- Laravel 12
- PHP 8.4
- Bootstrap 5
- Blade views
- Local development with Herd
- Authentication with Fortify

## Core project conventions
- Database, models, migrations, controllers, route names, variables, and technical naming are in English.
- UI labels, page titles, buttons, help text, and user-facing messages are in Italian.
- Follow existing Laravel conventions unless the project already uses a different clear pattern.
- Prefer simple, maintainable code over abstract or premature architecture.

## Business/domain rules
- `organizations` are the administrative entities (company, fund, institution, professional, private legal subject).
- `people` are individuals.
- A person does not store fiscal/administrative data.
- The connection between person and organization is modeled through `person_organization_relations`.
- A person may exist without any organization relation.
- A person may have multiple relations with the same organization over time or for different roles.
- `end_date` in person-organization relations cannot be earlier than `start_date`.
- `is_active` may be managed independently from date fields.
- Contacts must not be duplicated across tables:
  - contact data belongs in `contact_points`
  - addresses belong in `addresses`
  - notes belong in `notes`
- Polymorphic ownership uses controlled string values like:
  - `organization`
  - `person`
  - `person_organization_relation`
  - `lead`
- Never use Laravel class names inside `owner_type`.

## Current project direction
- The Organizations module already exists and is the current base of the CRM.
- The next module to implement is `people`, together with `person_organization_relations`.
- The implementation must prepare the system for future integration with:
  - contact_points
  - addresses
  - notes
- Do not invent new tables, new entities, or alternative structures outside the CRM data model.

## CRM data model to follow
Entities relevant now:
- organizations
- people
- person_organization_relations
- qualifications
- departments

Entities to prepare for later integration:
- contact_points
- addresses
- notes
- consents
- organization_role_assignments
- leads
- custom_fields

## What "Person" means in UI
Use Italian wording in UI:
- "Persone" for people index
- "Persona" for person detail/edit/create
- "Relazioni" or "Relazioni con organizzazioni" for person_organization_relations
- "Organizzazione" for linked organization

Do not present `person_organization_relations` as a technical table in UI.
Treat it as the structured relationship between a person and an organization.

## Implementation priorities for the next phase
When working on the People module, prefer this order:
1. Person model
2. Person CRUD
3. Person index
4. Person create/edit form
5. Person show page
6. Relationship management with organizations
7. Eager loading and cleanup
8. Future placeholders/hooks for contact points, addresses, notes

## UI/UX rules
- UI must be sober, clear, and CRM-like.
- Use Bootstrap 5 components and spacing consistently with the existing Organizations module.
- Reuse partials where it improves maintainability.
- Avoid excessive visual complexity.
- Prefer cards, tables, badges, tabs/sections only if they match the current project style.
- Keep forms readable and modular.
- In show pages, organize content in sections.

## Structure expectations
Prefer:
- thin controllers
- validation extracted when useful
- reusable Blade partials
- Eloquent relations used clearly
- route model binding when appropriate

Avoid:
- heavy business logic in Blade
- duplicated validation logic
- duplicated markup across create/edit/show when partials can solve it
- storing relation-specific fields directly on `people` or `organizations`

## Validation rules to respect
For `people`:
- `first_name` and `last_name` are the minimum data expected for a person
- keep validation aligned with current project style

For `person_organization_relations`:
- `person_id` required
- `organization_id` required
- `qualification_id` nullable
- `department_id` nullable
- `start_date` nullable unless current implementation requires it
- `end_date` nullable
- if both dates exist, `end_date >= start_date`
- `is_active` boolean

## Query/performance rules
- Use eager loading where useful in index/show pages.
- Avoid N+1 problems when loading organization relations.
- Keep queries readable.
- Do not over-engineer repositories/services unless the current codebase already uses them.

## Existing project alignment
- Treat the current Organizations module as the reference for coding style and UI structure.
- Keep naming coherent with the existing module.
- If the codebase still contains traces of "client" terminology, prefer aligning new code to `organization` as the main domain entity.
- Do not widen scope unless explicitly requested.

## When asked to change code
Before making non-trivial changes:
1. inspect the existing files involved
2. explain the plan briefly
3. modify only the necessary files
4. summarize changed files at the end

## When asked for analysis only
If the request is analytical:
- do not modify files
- do not propose hidden edits
- provide file-by-file observations and a concrete plan

## Files likely involved in the next phase
Typical areas to inspect before coding:
- `app/Models`
- `app/Http/Controllers`
- `resources/views`
- `routes/web.php`
- migrations for `people` and `person_organization_relations`
- lookup tables such as qualifications and departments

## Non-goals for this phase
Do not implement yet unless explicitly requested:
- full contact_points CRUD
- full addresses CRUD
- consents management
- leads conversion logic tied to people
- custom fields UI
- audit/activity/access logs UI
- advanced authorization/roles system
- tests beyond minimal necessary support

## Output style for coding tasks
When implementing:
- keep code production-oriented
- keep comments minimal and useful
- preserve consistency with the existing project
- prefer incremental changes over large rewrites