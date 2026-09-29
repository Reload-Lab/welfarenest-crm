<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\EnsureWnPlusAccount;
use App\Http\Middleware\VerifyWnPlusApiToken;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // API server-to-server per plus.welfarenest.it. Registrate a mano e non
        // con `artisan install:api`, che installerebbe Sanctum: l'autenticazione
        // qui e' un token condiviso, non servono token per utente.
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'wn-plus.account' => EnsureWnPlusAccount::class,
            'wn-plus.api' => VerifyWnPlusApiToken::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'wn-plus/oidc/token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();