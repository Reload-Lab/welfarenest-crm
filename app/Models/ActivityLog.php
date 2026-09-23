<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\AuditPresenter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'activity_type',
        'subject_type',
        'subject_id',
        'properties_json',
    ];

    protected $casts = [
        'properties_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo(__FUNCTION__, 'subject_type', 'subject_id');
    }

    // --- Presentazione (sezione Registri) ------------------------------------

    public function getActivityLabelAttribute(): string
    {
        return AuditPresenter::activityLabel($this->activity_type);
    }

    public function getSubjectLabelAttribute(): string
    {
        return AuditPresenter::entityLabel($this->subject_type);
    }

    public function getSubjectUrlAttribute(): ?string
    {
        return AuditPresenter::entityUrl($this->subject_type, $this->subject_id);
    }

    public function getActorLabelAttribute(): string
    {
        return AuditPresenter::actorLabel($this);
    }

    /**
     * Le proprieta senza le chiavi di contesto, che hanno gia una colonna
     * propria nella tabella a video.
     *
     * @return array<string, mixed>
     */
    public function getDetailsAttribute(): array
    {
        return collect($this->properties_json ?? [])
            ->except(['origin', 'ip', 'route', 'wn_plus_account_id'])
            ->all();
    }
}
