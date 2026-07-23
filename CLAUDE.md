# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Internal CRM built with Laravel 12 (PHP 8.2+, running on PHP 8.3/8.4 locally via Herd). Server-rendered with Blade + Bootstrap 5; Vite/Tailwind 4 pipeline for assets. Auth via Laravel Fortify.

## Commands

```bash
# Install
composer install
npm install

# Local dev (serves app + queue worker + vite, concurrently)
composer run dev

# Asset build
npm run dev      # vite dev server
npm run build    # production build

# Tests (Pest, run through artisan)
composer test          # clears config cache, then runs the full suite
php artisan test
php artisan test --filter=test_name_or_description
php artisan test tests/Feature/SomeTest.php

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Code style
vendor/bin/pint          # Laravel Pint (PSR-12-based formatter)
vendor/bin/pint --dirty  # only changed files
```

There is no JS/PHP linter beyond Pint configured in this repo (no ESLint/Larastan config present).

## Architecture

### Domain model

This is a CRM whose central entities are **organizations** and **people**, kept deliberately decoupled:

- `Organization` — the administrative/legal entity (company, fund, institution, professional, private legal subject). Holds fiscal data (`vat_number`, `tax_code`, `sdi_code`, split-payment flag).
- `Person` — an individual. **Never stores fiscal/administrative data.**
- `PersonOrganizationRelation` — the join model connecting a person to an organization for a `qualification`/`department`, with `start_date`/`end_date` (end cannot precede start) and an independent `is_active` flag. A person can have zero, one, or multiple relations (including multiple relations to the *same* organization over time or for different roles) and can exist with no organization at all.

Shared/polymorphic concerns hang off both `Organization` and `Person` via an `owner_type`/`owner_id` pair (never a Laravel FQCN — always a controlled string: `organization`, `person`, `person_organization_relation`, `lead`):
- `ContactPoint` (`contact_points`) — phone/email/etc., never duplicated into other tables.
- `Address` (`addresses`).
- `Note` (`notes`) — supports archive/restore and pinning.
- `Consent` / `ConsentType` / `ConsentVersion` (see Consents below).

`OrganizationRole` is attached to organizations via the `organization_role_assignments` pivot. `qualifications` and `departments` are lookup tables used by `person_organization_relations`.

The **Organizations module is the reference implementation** for coding style and UI structure — align new modules (People, and future contact_points/addresses/notes work) with its conventions rather than inventing new patterns. Do not introduce new tables/entities outside the modeled domain (see `docs/crm_data_model_v07.md`, `docs/crm_business_rules_v06.md`, `docs/crm_glossary_v06.md` for the full data model/business rules if deeper context is needed).

### Consents

`ConsentService` (`app/Services/ConsentService.php`) grants/denies/looks up consents by `(owner_type, owner_id, consent_type_code)`, always resolving the currently-active `ConsentVersion` for that `ConsentType`. `HasConsents` (`app/Models/Concerns/HasConsents.php`) is a trait for owner models (used by `Person`). `ConsentRequest` + `ConsentRequestService` implement an out-of-band flow where a consent request is sent (public link at `consent-requests/{token}`) and completed without authentication — see `ConsentRequestController`.

### WN Plus subsystem

`WnPlus*` models/controllers are a distinct sub-product bolted onto the CRM: external accounts (`WnPlusAccount`, roles/levels) tied to an `Organization`, an invitation flow (`WnPlusInvitation`), its own login (`WnPlusAuthController`, session-based, separate from the main app's Fortify auth), and a self-hosted **OIDC provider** (`WnPlusOidcController`) exposing `/.well-known/openid-configuration`, `authorize`, `token`, `jwks`, `userinfo` for third parties to log in against WN Plus accounts. The OIDC token endpoint is CSRF-exempt (see `bootstrap/app.php`). Treat this as a separate bounded area from the core organizations/people CRM — don't cross-wire its models into core CRM business rules.

### Page metadata (titles/breadcrumbs)

`config/crm_pages.php` maps route names to `title`/`breadcrumbs` definitions (values may be closures receiving the route params, e.g. to render an organization's display name). `App\Support\CrmPage::resolve()` reads the current route and looks up its entry; layouts consume this instead of hardcoding titles/breadcrumbs per view.

### Icons

`config/icons.php` maps semantic icon keys (grouped by area: `actions`, `navigation`, ...) to Blade icon partial paths, rendered through `resources/views/components/icon.blade.php`. Add new icons there rather than hardcoding SVG/paths in views. `/dev/icons` (dev-only route) renders the full icon set for reference.

### Conventions (from `docs/AGENTS.md`)

- Database/model/migration/controller/route names and all technical identifiers: **English**.
- UI labels, page titles, buttons, help text, user-facing messages: **Italian** (e.g. "Persone", "Persona", "Organizzazione", "Relazioni con organizzazioni" — never expose `person_organization_relations` as a technical table name in the UI).
- Thin controllers, Eloquent relations used directly, route model binding, reusable Blade partials for repeated create/edit/show markup.
- Avoid business logic in Blade; avoid duplicated validation logic.
- Don't add repository/service layers unless the codebase already uses one for that area (Consents is the existing exception — see above).
- Eager-load relations in index/show pages to avoid N+1s (e.g. organization relations, contact points).

## Tests

Uses Pest (`pestphp/pest` + `pest-plugin-laravel`) with a PHPUnit bootstrap (`tests/Pest.php` binds `Tests\TestCase` for `Feature`). `phpunit.xml` configures an in-memory SQLite DB and array session/cache/queue drivers for the test environment — no separate test DB setup needed. Existing test files under `tests/Feature` and `tests/Unit` are the default Pest scaffolding (`ExampleTest.php`) rather than real coverage yet.
