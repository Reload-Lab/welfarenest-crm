<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\AuditPresenter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'event_type',
        'auditable_type',
        'auditable_id',
        'old_values_json',
        'new_values_json',
        'context_json',
    ];

    protected $casts = [
        'old_values_json' => 'array',
        'new_values_json' => 'array',
        'context_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo(__FUNCTION__, 'auditable_type', 'auditable_id');
    }

    // --- Presentazione (sezione Registri) ------------------------------------

    public function getEntityLabelAttribute(): string
    {
        return AuditPresenter::entityLabel($this->auditable_type);
    }

    public function getEntityUrlAttribute(): ?string
    {
        return AuditPresenter::entityUrl($this->auditable_type, $this->auditable_id);
    }

    public function getEventLabelAttribute(): string
    {
        return AuditPresenter::eventLabel($this->event_type);
    }

    public function getEventVariantAttribute(): string
    {
        return AuditPresenter::eventVariant($this->event_type);
    }

    public function getActorLabelAttribute(): string
    {
        return AuditPresenter::actorLabel($this);
    }

    public function getOriginLabelAttribute(): ?string
    {
        return AuditPresenter::originLabel($this->context_json['origin'] ?? null);
    }

    /**
     * @return array<int, array{field: string, label: string, old: string, new: string}>
     */
    public function getChangeListAttribute(): array
    {
        return AuditPresenter::changes($this);
    }

    /**
     * Elenco dei campi modificati, per la schermata di consultazione.
     *
     * @return array<int, string>
     */
    public function changedAttributes(): array
    {
        return array_values(array_unique(array_merge(
            array_keys($this->old_values_json ?? []),
            array_keys($this->new_values_json ?? []),
        )));
    }
}
