<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PersonOrganizationRelationController;
use App\Http\Controllers\ContactPointController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\WnPlusAccountController;
use App\Http\Controllers\WnPlusInvitationController;
use App\Http\Controllers\WnPlusAuthController;

use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\ContactPoint;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/clients', [OrganizationController::class, 'clients'])->name('clients.index');
    Route::get('/clients/{organization}', [OrganizationController::class, 'show'])->name('clients.show');

    Route::get('/suppliers', [OrganizationController::class, 'suppliers'])->name('suppliers.index');
    Route::get('/suppliers/{organization}', [OrganizationController::class, 'show'])->name('suppliers.show');

    Route::get('organizations/search', [OrganizationController::class, 'search'])->name('organizations.search');
    Route::resource('organizations', OrganizationController::class);
    Route::get('people/search', [PersonController::class, 'search'])->name('people.search');
    //Route::resource('people', PersonController::class)->except(['destroy']);
    Route::resource('people', PersonController::class);

    Route::post('people/{person}/relations', [PersonOrganizationRelationController::class, 'store'])->name('people.relations.store');
    Route::post('organizations/{organization}/relations', [PersonOrganizationRelationController::class, 'storeFromOrganization'])->name('organizations.relations.store');
    Route::put('people/{person}/relations/{relation}', [PersonOrganizationRelationController::class, 'update'])->name('people.relations.update');
    Route::put('organizations/{organization}/relations/{relation}', [PersonOrganizationRelationController::class, 'updateFromOrganization'])->name('organizations.relations.update');

    Route::delete(
        'organizations/{organization}/relations/{relation}',
        [PersonOrganizationRelationController::class, 'destroyFromOrganization']
    )->name('organizations.relations.destroy');

    Route::delete(
        'people/{person}/relations/{relation}',
        [PersonOrganizationRelationController::class, 'destroy']
    )->name('people.relations.destroy');

    Route::post('organizations/{organization}/contact-points', [ContactPointController::class, 'storeForOrganization'])->name('organizations.contact-points.store');
    Route::post('people/{person}/contact-points', [ContactPointController::class, 'storeForPerson'])->name('people.contact-points.store');   
    Route::delete('contact-points/{contactPoint}', [ContactPointController::class, 'destroy'])->name('contact-points.destroy');

    Route::put('contact-points/{contactPoint}', [ContactPointController::class, 'update'])->name('contact-points.update');

    Route::post(
        'people/{person}/relations/{relation}/contact-points',
        [ContactPointController::class, 'storeForRelation']
    )->name('people.relations.contact-points.store');    

    Route::post('/organizations/{organization}/addresses', [AddressController::class, 'storeForOrganization'])
        ->name('organizations.addresses.store');

    Route::put('/organizations/{organization}/addresses/{address}', [AddressController::class, 'updateForOrganization'])
        ->name('organizations.addresses.update');

    Route::delete('/organizations/{organization}/addresses/{address}', [AddressController::class, 'destroyForOrganization'])
        ->name('organizations.addresses.destroy');

    Route::post('/organizations/{organization}/notes', [NoteController::class, 'store'])
        ->name('organizations.notes.store');

    Route::patch('/notes/{note}/archive', [NoteController::class, 'archive'])
        ->name('notes.archive');

    Route::patch('/notes/{note}/restore', [NoteController::class, 'restore'])
        ->name('notes.restore');


    Route::patch('/notes/{note}/toggle-pinned', [NoteController::class, 'togglePinned'])
        ->name('notes.toggle-pinned');

    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])
        ->name('notes.destroy');

    Route::resource('wn-plus/accounts', WnPlusAccountController::class)
        ->names('wn-plus.accounts');

    Route::get('/wn-plus/accounts/{account}/users/create', [WnPlusAccountController::class, 'createUser'])
        ->name('wn-plus.accounts.users.create');

    Route::post('/wn-plus/accounts/{account}/users', [WnPlusAccountController::class, 'storeUser'])
        ->name('wn-plus.accounts.users.store');

    Route::post('/wn-plus/accounts/{account}/invite', [WnPlusAccountController::class, 'sendInvitation'])
        ->name('wn-plus.accounts.invite');

});

Route::get('/wn-plus/invitations/{token}', [WnPlusInvitationController::class, 'accept'])
    ->name('wn-plus.invitations.accept');

Route::post('/wn-plus/invitations/{token}', [WnPlusInvitationController::class, 'complete'])
    ->name('wn-plus.invitations.complete');


Route::get('/wn-plus/login', [WnPlusAuthController::class, 'showLogin'])
    ->name('wn-plus.login');

Route::post('/wn-plus/login', [WnPlusAuthController::class, 'login'])
    ->name('wn-plus.login.post');

Route::post('/wn-plus/logout', [WnPlusAuthController::class, 'logout'])
    ->name('wn-plus.logout');


Route::get('/wn-plus/portal', function () {
    $account = App\Models\WnPlusAccount::with(['organization', 'role', 'level'])
        ->findOrFail(session('wn_plus_account_id'));

    return view('wn-plus.portal.dashboard', compact('account'));
})->name('wn-plus.portal.dashboard');


//DEV
Route::middleware(['auth'])->get('/dev/icons', function () {
    $icons = config('icons');
    return view('dev.icons.index', compact('icons'));
})->name('dev.icons');