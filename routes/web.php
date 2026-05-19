<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PersonOrganizationRelationController;
use App\Http\Controllers\ContactPointController;
use App\Http\Controllers\AddressController;


use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\ContactPoint;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        $stats = [
            [
                'label' => 'Organizzazioni',
                'value' => Organization::count(),
                'icon_group' => 'entities',
                'icon_name' => 'organization',
                'tone' => 'blue',
            ],
            [
                'label' => 'Persone',
                'value' => Person::count(),
                'icon_group' => 'entities',
                'icon_name' => 'person',
                'tone' => 'teal',
            ],
            [
                'label' => 'Relazioni',
                'value' => PersonOrganizationRelation::count(),
                'icon_group' => 'entities',
                'icon_name' => 'relation',
                'tone' => 'indigo',
            ],
            [
                'label' => 'Recapiti',
                'value' => ContactPoint::count(),
                'icon_group' => 'contact',
                'icon_name' => 'contact_point',
                'tone' => 'orange',
            ],
        ];

        return view('dashboard', compact('stats'));
    })->name('dashboard');

    Route::get('/clients', [OrganizationController::class, 'clients'])->name('clients.index');
    Route::get('/suppliers', [OrganizationController::class, 'suppliers'])->name('suppliers.index');

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


    Route::post('/organizations/{organization}/addresses', [AddressController::class, 'storeForOrganization'])
        ->name('organizations.addresses.store');

    Route::put('/organizations/{organization}/addresses/{address}', [AddressController::class, 'updateForOrganization'])
        ->name('organizations.addresses.update');

    Route::delete('/organizations/{organization}/addresses/{address}', [AddressController::class, 'destroyForOrganization'])
        ->name('organizations.addresses.destroy');


});


//DEV
Route::middleware(['auth'])->get('/dev/icons', function () {
    $icons = config('icons');
    return view('dev.icons.index', compact('icons'));
})->name('dev.icons');