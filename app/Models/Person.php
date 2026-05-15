<?php

namespace App\Models;

use App\Models\ContactPoint;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Person extends Model
{
    use HasFactory;

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
        

}
