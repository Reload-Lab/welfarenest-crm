<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationRole extends Model
{
    use HasFactory;

    protected $table = 'organization_roles';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organizations()
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_role_assignments',
            'organization_role_id',
            'organization_id'
        );
    }
}