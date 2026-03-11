<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/clients', [ClientController::class, 'index']);
Route::get('/clients/create', [ClientController::class, 'create']);
Route::post('/clients', [ClientController::class, 'store']);
Route::get('/clients/{client}/edit', [ClientController::class, 'edit']);
Route::put('/clients/{client}', [ClientController::class, 'update']);