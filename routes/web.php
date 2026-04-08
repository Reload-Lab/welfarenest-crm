<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\PersonOrganizationRelationController;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    
Route::middleware(['auth'])->group(function () {
    Route::resource('organizations', OrganizationController::class);
    Route::resource('people', PersonController::class)->except(['destroy']);
    Route::post('people/{person}/relations', [PersonOrganizationRelationController::class, 'store'])->name('people.relations.store');
    Route::put('people/{person}/relations/{relation}', [PersonOrganizationRelationController::class, 'update'])->name('people.relations.update');
});

    Route::get('/organizations/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
});
