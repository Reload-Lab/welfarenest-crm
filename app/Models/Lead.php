<?php

namespace App\Models;

use App\Models\Concerns\HasConsents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    use HasConsents;

    protected $table = 'leads';

    protected $fillable = [
        'organization_id',
        'person_id',
        'lead_status_id',
        'lead_source_id',
        'name',
        'first_name',
        'last_name',
        'company_name',
        'description',
        'estimated_value',
        'expected_close_date',
        'assigned_user_id',
        'is_active',
        'closed_at',
        'lost_reason',
        'converted_at',
        'converted_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_value' => 'decimal:2',
        'expected_close_date' => 'date',
        'closed_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    /**
     * Anagrafiche collegate. Sono valorizzate o alla creazione (lead nato su
     * un'anagrafica già esistente) o alla conversione: è `converted_at` a
     * distinguere i due casi.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function convertedByUser()
    {
        return $this->belongsTo(User::class, 'converted_by_user_id');
    }

    /**
     * Recapiti del lead. Alla conversione non vengono copiati: viene
     * riscritto owner_type/owner_id sui record esistenti, così lo storico
     * del recapito resta intatto.
     */
    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class, 'owner_id')
            ->where('owner_type', 'lead');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'owner_id')
            ->where('owner_type', 'lead');
    }

    /**
     * Nome e cognome dichiarati dal contatto. Può essere vuoto: un lead
     * può arrivare con la sola azienda o il solo recapito.
     */
    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->last_name,
        ])->filter()->implode(' '));
    }

    /**
     * Etichetta da mostrare in elenco: la persona se la conosciamo,
     * altrimenti il titolo del lead.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name !== '' ? $this->full_name : $this->name;
    }

    public function isConverted(): bool
    {
        return $this->converted_at !== null;
    }

    public function isClosed(): bool
    {
        return (bool) $this->status?->is_final;
    }

    public function scopeOpen($query)
    {
        return $query->whereHas('status', fn ($q) => $q->where('is_won', false)->where('is_lost', false));
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }
}
