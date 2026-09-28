<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\Concerns\Auditable;

class Consent extends Model
{
    use Auditable;

    protected $fillable = [

        'owner_type',
        'owner_id',

        'consent_type_id',
        'consent_version_id',

        'status',

        'requested_at',
        'granted_at',
        'revoked_at',
        'denied_at',

        'source',

        'created_by_user_id',

        'notes',
        'evidence_file_path',
    ];

    protected $casts = [

        'requested_at' => 'datetime',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'denied_at' => 'datetime',

    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function consentType()
    {
        return $this->belongsTo(ConsentType::class);
    }

    public function consentVersion()
    {
        return $this->belongsTo(ConsentVersion::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quando è avvenuto il fatto, non quando è stata scritta la riga: la
     * colonna cambia a seconda dello stato. Per i consensi pregressi inseriti
     * a mano è una data anteriore a `created_at`, ed è corretto che lo sia.
     */
    public function getEffectiveAtAttribute(): ?Carbon
    {
        return $this->granted_at
            ?? $this->denied_at
            ?? $this->revoked_at
            ?? $this->created_at;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'granted' => 'Concesso',
            'denied' => 'Negato',
            'revoked' => 'Revocato',
            default => (string) $this->status,
        };
    }

    public function getStatusVariantAttribute(): string
    {
        return match ($this->status) {
            'granted' => 'success',
            'denied' => 'danger',
            'revoked' => 'warning',
            default => 'muted',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        if (blank($this->source)) {
            return 'Origine non registrata';
        }

        return config('consent_sources.'.$this->source.'.label', $this->source);
    }

    /**
     * Vero se la riga è stata digitata da un operatore sulla base di una prova
     * esterna, invece di nascere da una scelta fatta dall'interessato in un
     * flusso digitale. Il registro deve mostrarlo.
     */
    public function getIsManualAttribute(): bool
    {
        return (bool) config('consent_sources.'.$this->source.'.manual', false);
    }
}
