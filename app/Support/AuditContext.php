<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Determina chi ha eseguito l'operazione e da dove.
 *
 * user_id e valorizzato solo quando c'e un utente CRM autenticato. In tutti
 * gli altri casi resta NULL e l'origine dell'operazione viene registrata nel
 * contesto, cosi da sapere comunque cosa ha agito sul dato.
 */
class AuditContext
{
    public const ORIGIN_WEB = 'web';
    public const ORIGIN_CONSOLE = 'console';
    public const ORIGIN_IMPORT = 'import';
    public const ORIGIN_WN_PLUS = 'wn_plus';
    public const ORIGIN_PUBLIC_CONSENT = 'public_consent';
    public const ORIGIN_SYSTEM = 'system';

    /** @var array<int, array<string, mixed>> */
    protected static array $stack = [];

    /**
     * Esegue il callback marcando tutte le scritture di log con un contesto
     * esplicito. Usato dalle operazioni che non nascono da una richiesta HTTP
     * dell'utente (import massivo, comandi artisan, job).
     *
     * @param  array<string, mixed>  $context
     */
    public static function within(array $context, callable $callback): mixed
    {
        static::$stack[] = $context;

        try {
            return $callback();
        } finally {
            array_pop(static::$stack);
        }
    }

    public static function actorId(): ?int
    {
        $id = Auth::id();

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        $context = array_merge(
            ['origin' => static::detectOrigin()],
            static::overrides(),
        );

        if ($accountId = static::wnPlusAccountId()) {
            $context['wn_plus_account_id'] = $accountId;
        }

        if (! app()->runningInConsole() && $request = static::request()) {
            $context['ip'] = $request->ip();
            $context['route'] = $request->route()?->getName();
        }

        return array_filter($context, static fn ($value) => $value !== null && $value !== []);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function overrides(): array
    {
        return static::$stack === [] ? [] : end(static::$stack);
    }

    /**
     * L'ordine conta. runningInConsole() e vero anche durante i test, quindi
     * non puo essere il primo controllo: si guarda prima chi sta agendo, poi
     * da quale rotta, e solo alla fine si ripiega su console/system.
     */
    protected static function detectOrigin(): string
    {
        $overrides = static::overrides();

        if (isset($overrides['origin'])) {
            return (string) $overrides['origin'];
        }

        if (static::actorId() !== null) {
            return static::ORIGIN_WEB;
        }

        if (static::wnPlusAccountId() !== null) {
            return static::ORIGIN_WN_PLUS;
        }

        $routeName = static::request()?->route()?->getName();

        if (is_string($routeName) && str_starts_with($routeName, 'consent-requests.')) {
            return static::ORIGIN_PUBLIC_CONSENT;
        }

        if (app()->runningInConsole()) {
            return static::ORIGIN_CONSOLE;
        }

        return static::ORIGIN_SYSTEM;
    }

    protected static function wnPlusAccountId(): ?int
    {
        $request = static::request();

        if (! $request || ! $request->hasSession()) {
            return null;
        }

        $id = $request->session()->get('wn_plus_account_id');

        return is_numeric($id) ? (int) $id : null;
    }

    protected static function request(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = app('request');

        return $request instanceof Request ? $request : null;
    }
}
