<?php

namespace App\Support;

use App\Models\AccessLog;

/**
 * Registra gli accessi: login riusciti, tentativi falliti, logout.
 * Vale sia per gli utenti CRM (Fortify) sia per gli account WN+.
 */
class AccessLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function record(string $eventType, ?int $userId = null, array $context = []): AccessLog
    {
        $request = request();

        return AccessLog::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'context_json' => $context === [] ? null : $context,
        ]);
    }
}
