<?php

namespace App\Listeners;

use App\Models\AccessLog;
use App\Support\AccessLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

/**
 * Traduce gli eventi di autenticazione di Laravel/Fortify in righe di
 * access_logs.
 *
 * I metodi si chiamano on*() e non handle*(): Laravel scopre e registra
 * automaticamente i listener in app/Listeners guardando i metodi che
 * iniziano per "handle", e con Event::subscribe() in AppServiceProvider
 * finirebbero registrati due volte, raddoppiando ogni riga di log. Il login WN+ non passa da questi eventi (ha una sessione
 * propria) ed e registrato direttamente in WnPlusAuthController.
 */
class AccessLogSubscriber
{
    public function onLogin(Login $event): void
    {
        AccessLogger::record(
            AccessLog::EVENT_LOGIN,
            $this->userId($event->user),
            ['guard' => $event->guard],
        );
    }

    public function onFailed(Failed $event): void
    {
        AccessLogger::record(
            AccessLog::EVENT_LOGIN_FAILED,
            $this->userId($event->user),
            array_filter([
                'guard' => $event->guard,
                'email' => $event->credentials['email'] ?? null,
            ]),
        );
    }

    public function onLogout(Logout $event): void
    {
        AccessLogger::record(
            AccessLog::EVENT_LOGOUT,
            $this->userId($event->user),
            ['guard' => $event->guard],
        );
    }

    public function onLockout(Lockout $event): void
    {
        AccessLogger::record(
            'login_lockout',
            null,
            array_filter([
                'email' => $event->request->input('email'),
            ]),
        );
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'onLogin',
            Failed::class => 'onFailed',
            Logout::class => 'onLogout',
            Lockout::class => 'onLockout',
        ];
    }

    protected function userId(mixed $user): ?int
    {
        $id = $user?->getAuthIdentifier();

        return is_numeric($id) ? (int) $id : null;
    }
}
