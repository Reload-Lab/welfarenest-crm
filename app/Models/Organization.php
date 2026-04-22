<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrganizationType;
use App\Models\OrganizationRole;
use App\Models\ContactPoint;


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



}

