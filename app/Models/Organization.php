<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrganizationType;
use App\Models\OrganizationRole;
use App\Models\ContactPoint;
use App\Models\Address;
use App\Models\PersonOrganizationRelation;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'legal_name',
        'organization_type_id',
        'vat_number',
        'tax_code',
        'sdi_code',
        'is_split_payment',
        'is_active',
    ];

    protected $casts = [
        'is_split_payment' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relazione con OrganizationType
     */
    public function organizationType()
    {
        return $this->belongsTo(OrganizationType::class);
    }

    public function organizationRoles()
    {
        return $this->belongsToMany(
            OrganizationRole::class,
            'organization_role_assignments',
            'organization_id',
            'organization_role_id'
        );
    }

    public function personOrganizationRelations()
    {
        return $this->hasMany(PersonOrganizationRelation::class);
    }

    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class, 'owner_id')
            ->where('owner_type', 'organization');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->legal_name ?: 'Organizzazione';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : null;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'owner_id')
            ->where('owner_type', 'organization')
            ->with('addressType')
            ->orderByDesc('is_primary')
            ->orderBy('address_type_id')
            ->orderBy('city');
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\Note::class, 'owner_id')
            ->where('owner_type', 'organization')
            ->latest('created_at');
    }

    public function wnPlusAccounts(): HasMany
    {
        return $this->hasMany(WnPlusAccount::class);
    }

    public function personRelations(): HasMany
    {
        return $this->hasMany(PersonOrganizationRelation::class);
    }


}

