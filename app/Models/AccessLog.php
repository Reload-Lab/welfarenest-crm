<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\AuditPresenter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLog extends Model
{
    public const UPDATED_AT = null;

    public const EVENT_LOGIN = 'login';
    public const EVENT_LOGIN_FAILED = 'login_failed';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_WN_PLUS_LOGIN = 'wn_plus_login';
    public const EVENT_WN_PLUS_LOGIN_FAILED = 'wn_plus_login_failed';
    public const EVENT_WN_PLUS_LOGOUT = 'wn_plus_logout';

    protected $table = 'access_logs';

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'context_json',
    ];

    protected $casts = [
        'context_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Presentazione (sezione Registri) ------------------------------------

    public function getEventLabelAttribute(): string
    {
        return AuditPresenter::accessEventLabel($this->event_type);
    }

    public function getEventVariantAttribute(): string
    {
        return AuditPresenter::accessEventVariant($this->event_type);
    }

    /**
     * Chi ha tentato o effettuato l'accesso: l'utente CRM quando e noto,
     * altrimenti l'email usata nel tentativo.
     */
    public function getActorLabelAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->context_json['email'] ?? 'Sconosciuto';
    }
}
