<?php

namespace App\Models;

use App\Models\ContactPoint;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasConsents;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;
    use HasConsents;

    protected $table = 'people';

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    public function organizationRelations()
    {
        return $this->hasMany(PersonOrganizationRelation::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->last_name,
        ])->filter()->implode(' '));
    }

    public function getDisplayNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : null;
    }

    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class, 'owner_id')
            ->where('owner_type', 'person');
    }

    /**
     * Email di riferimento per l'invio della richiesta di consenso: la email
     * primaria se impostata, altrimenti la prima email disponibile (per id).
     * Su questo indirizzo, e solo su questo, viene inviata la richiesta di
     * consenso/informativa alla persona — semplificazione richiesta dalla DPO
     * per evitare più richieste parallele sulla stessa persona.
     */
    public function primaryOrFirstEmailContactPoint(): ?ContactPoint
    {
        return $this->contactPoints()
            ->whereHas('contactType', fn ($query) => $query->where('category', 'email'))
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();
    }

    public function wnPlusAccounts(): HasMany
    {
        return $this->hasMany(WnPlusAccount::class);
    }    

}
