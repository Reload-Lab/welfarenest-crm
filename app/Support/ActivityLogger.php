<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Registra le operazioni funzionali del sistema (cosa e stato fatto),
 * distinte dalle modifiche ai dati registrate su audit_logs.
 */
class ActivityLogger
{
    public const CONSENT_REQUEST_SENT = 'consent_request_sent';
    public const CONSENT_REQUEST_COMPLETED = 'consent_request_completed';
    public const CONSENT_GRANTED = 'consent_granted';
    public const CONSENT_DENIED = 'consent_denied';
    public const CONSENT_REVOKED = 'consent_revoked';
    public const WN_PLUS_INVITATION_SENT = 'wn_plus_invitation_sent';
    public const ORGANIZATION_IMPORT_RUN = 'organization_import_run';
    public const ORGANIZATION_IMPORT_ROLLED_BACK = 'organization_import_rolled_back';

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(string $activityType, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => AuditContext::actorId(),
            'activity_type' => $activityType,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties_json' => array_merge(AuditContext::current(), $properties),
        ]);
    }
}
