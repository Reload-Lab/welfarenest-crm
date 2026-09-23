<?php

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Person;
use App\Models\User;
use App\Support\AuditPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function logViewerUser(): User
{
    return User::create([
        'name' => 'Operatore',
        'email' => 'operatore@example.test',
        'password' => bcrypt('secret-password'),
    ]);
}

it('non e raggiungibile senza autenticazione', function () {
    $this->get(route('logs.audit'))->assertRedirect(route('login'));
    $this->get(route('logs.activity'))->assertRedirect(route('login'));
    $this->get(route('logs.access'))->assertRedirect(route('login'));
});

it('mostra le modifiche con etichette in italiano', function () {
    $user = logViewerUser();
    $this->actingAs($user);

    $person = Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $person->update(['first_name' => 'Maria']);

    $this->get(route('logs.audit'))
        ->assertOk()
        ->assertSee('Modifica')
        ->assertSee('Persona')
        ->assertSee('Nome')
        ->assertSee('Mario')
        ->assertSee('Maria');
});

it('filtra per entita', function () {
    $this->actingAs(logViewerUser());

    $type = OrganizationType::create(['code' => 'fondo', 'name' => 'Fondo pensione']);
    Organization::create(['name' => 'Fondo Alfa', 'organization_type_id' => $type->id]);
    Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);

    $this->get(route('logs.audit', ['entity_type' => 'organization']))
        ->assertOk()
        ->assertSee('Fondo Alfa')
        ->assertDontSee('Mario');
});

it('filtra le operazioni senza utente', function () {
    $user = logViewerUser();

    // Creata senza utente autenticato: resta a carico dell'origine automatica.
    Person::create(['first_name' => 'Automatica', 'last_name' => 'Rossi']);

    $this->actingAs($user);
    Person::create(['first_name' => 'Manuale', 'last_name' => 'Bianchi']);

    $this->get(route('logs.audit', ['user_id' => 'none']))
        ->assertOk()
        ->assertSee('Automatica')
        ->assertDontSee('Manuale');
});

it('risolve le chiavi esterne nel nome leggibile', function () {
    $this->actingAs(logViewerUser());

    $type = OrganizationType::create(['code' => 'fondo', 'name' => 'Fondo pensione']);
    $altro = OrganizationType::create(['code' => 'ente', 'name' => 'Ente pubblico']);

    $organization = Organization::create(['name' => 'Fondo Alfa', 'organization_type_id' => $type->id]);
    $organization->update(['organization_type_id' => $altro->id]);

    $log = AuditLog::where('event_type', 'updated')->sole();

    $this->get(route('logs.audit.show', $log))
        ->assertOk()
        ->assertSee('Tipo organizzazione')
        ->assertSee('Fondo pensione')
        ->assertSee('Ente pubblico')
        // l'id grezzo non deve comparire al posto del nome
        ->assertDontSee('organization_type_id');
});

it('mostra il registro attivita e quello degli accessi', function () {
    $this->actingAs(logViewerUser());

    ActivityLog::create([
        'user_id' => null,
        'activity_type' => 'consent_request_sent',
        'properties_json' => ['origin' => 'web', 'email' => 'mario@example.test'],
    ]);

    AccessLog::create([
        'user_id' => null,
        'event_type' => AccessLog::EVENT_LOGIN_FAILED,
        'ip_address' => '10.0.0.9',
        'context_json' => ['email' => 'ignoto@example.test'],
    ]);

    $this->get(route('logs.activity'))
        ->assertOk()
        ->assertSee('Richiesta di consenso inviata');

    $this->get(route('logs.access'))
        ->assertOk()
        ->assertSee('Accesso fallito')
        ->assertSee('10.0.0.9')
        ->assertSee('ignoto@example.test');
});

it('segnala i riferimenti a record cancellati invece di mostrare un id nudo', function () {
    $type = OrganizationType::create(['code' => 'fondo', 'name' => 'Fondo pensione']);

    expect(AuditPresenter::formatValue('organization', 'organization_type_id', $type->id))
        ->toBe('Fondo pensione');

    expect(AuditPresenter::formatValue('organization', 'organization_type_id', 99999))
        ->toContain('non più presente');
});
