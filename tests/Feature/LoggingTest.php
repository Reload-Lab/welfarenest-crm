<?php

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Person;
use App\Models\User;
use App\Models\WnPlusAccount;
use App\Models\WnPlusLevel;
use App\Models\WnPlusRole;
use App\Support\AccessLogger;
use App\Support\ActivityLogger;
use App\Support\AuditContext;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeOrganization(array $attributes = []): Organization
{
    $type = OrganizationType::create([
        'code' => 'fondo_'.uniqid(),
        'name' => 'Fondo pensione',
    ]);

    return Organization::create(array_merge([
        'name' => 'Fondo Alfa',
        'organization_type_id' => $type->id,
        'is_active' => true,
    ], $attributes));
}

// --- Audit log ---------------------------------------------------------------

it('registra la creazione di una organizzazione', function () {
    $organization = makeOrganization();

    $log = AuditLog::where('auditable_type', 'organization')
        ->where('auditable_id', $organization->id)
        ->where('event_type', 'created')
        ->sole();

    expect($log->new_values_json['name'])->toBe('Fondo Alfa')
        ->and($log->old_values_json)->toBeNull()
        ->and($log->context_json['origin'])->not->toBeNull();
});

it('registra solo i campi effettivamente modificati', function () {
    $organization = makeOrganization();
    AuditLog::query()->delete();

    $organization->update(['name' => 'Fondo Beta']);

    $log = AuditLog::where('event_type', 'updated')->sole();

    expect(array_keys($log->new_values_json))->toBe(['name'])
        ->and($log->old_values_json['name'])->toBe('Fondo Alfa')
        ->and($log->new_values_json['name'])->toBe('Fondo Beta');
});

it('non registra nulla se il salvataggio non cambia alcun valore', function () {
    $organization = makeOrganization();
    AuditLog::query()->delete();

    $organization->update(['name' => 'Fondo Alfa']);

    expect(AuditLog::count())->toBe(0);
});

it('registra la cancellazione conservando i valori precedenti', function () {
    $organization = makeOrganization();
    $id = $organization->id;
    AuditLog::query()->delete();

    $organization->delete();

    $log = AuditLog::where('event_type', 'deleted')->sole();

    expect($log->auditable_id)->toBe($id)
        ->and($log->old_values_json['name'])->toBe('Fondo Alfa')
        ->and($log->new_values_json)->toBeNull();
});

it('registra l utente autenticato come autore', function () {
    $user = User::create([
        'name' => 'Operatore',
        'email' => 'operatore@example.test',
        'password' => bcrypt('secret-password'),
    ]);

    $this->actingAs($user);

    $person = Person::create(['first_name' => 'Mario', 'last_name' => 'Rossi']);

    $log = AuditLog::where('auditable_type', 'person')
        ->where('auditable_id', $person->id)
        ->sole();

    expect($log->user_id)->toBe($user->id)
        ->and($log->context_json['origin'])->toBe(AuditContext::ORIGIN_WEB);
});

it('marca l origine quando l operazione non parte da un utente CRM', function () {
    AuditContext::within(['origin' => AuditContext::ORIGIN_IMPORT, 'import_file' => 'clienti.xlsx'], function () {
        makeOrganization(['name' => 'Fondo Importato']);
    });

    $log = AuditLog::where('event_type', 'created')
        ->where('auditable_type', 'organization')
        ->sole();

    expect($log->user_id)->toBeNull()
        ->and($log->context_json['origin'])->toBe(AuditContext::ORIGIN_IMPORT)
        ->and($log->context_json['import_file'])->toBe('clienti.xlsx');
});

it('non registra mai la password negli audit log', function () {
    $organization = makeOrganization();

    $role = WnPlusRole::create(['code' => 'user', 'name' => 'Utente']);
    $level = WnPlusLevel::create(['code' => 'base', 'name' => 'Base']);

    $account = WnPlusAccount::create([
        'uuid' => (string) Str::uuid(),
        'organization_id' => $organization->id,
        'first_name' => 'Anna',
        'last_name' => 'Bianchi',
        'email' => 'anna@example.test',
        'password' => bcrypt('secret-password'),
        'wn_plus_role_id' => $role->id,
        'wn_plus_level_id' => $level->id,
        'status' => 'active',
        'account_type' => 'user',
    ]);

    $log = AuditLog::where('auditable_type', 'wn_plus_account')
        ->where('auditable_id', $account->id)
        ->sole();

    expect($log->new_values_json)->not->toHaveKey('password')
        ->and($log->new_values_json['email'])->toBe('anna@example.test');
});

// --- Access log --------------------------------------------------------------

it('registra login, logout e tentativi falliti', function () {
    $user = User::create([
        'name' => 'Operatore',
        'email' => 'operatore@example.test',
        'password' => bcrypt('secret-password'),
    ]);

    event(new Login('web', $user, false));
    event(new Failed('web', null, ['email' => 'sconosciuto@example.test']));
    event(new Logout('web', $user));

    expect(AccessLog::where('event_type', AccessLog::EVENT_LOGIN)->where('user_id', $user->id)->count())->toBe(1)
        ->and(AccessLog::where('event_type', AccessLog::EVENT_LOGOUT)->where('user_id', $user->id)->count())->toBe(1);

    $failed = AccessLog::where('event_type', AccessLog::EVENT_LOGIN_FAILED)->sole();

    expect($failed->user_id)->toBeNull()
        ->and($failed->context_json['email'])->toBe('sconosciuto@example.test');
});

it('registra gli accessi del portale WN+', function () {
    AccessLogger::record(AccessLog::EVENT_WN_PLUS_LOGIN, null, ['wn_plus_account_id' => 42]);

    $log = AccessLog::where('event_type', AccessLog::EVENT_WN_PLUS_LOGIN)->sole();

    expect($log->user_id)->toBeNull()
        ->and($log->context_json['wn_plus_account_id'])->toBe(42);
});

// --- Activity log ------------------------------------------------------------

it('registra una attivita con soggetto e proprieta', function () {
    $organization = makeOrganization();

    ActivityLogger::log('test_activity', $organization, ['nota' => 'prova']);

    $log = ActivityLog::where('activity_type', 'test_activity')->sole();

    expect($log->subject_type)->toBe('organization')
        ->and($log->subject_id)->toBe($organization->id)
        ->and($log->properties_json['nota'])->toBe('prova')
        ->and($log->properties_json)->toHaveKey('origin');
});
