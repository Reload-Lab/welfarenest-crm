<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationType extends Model
{
    protected $fillable = [
        'name'
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}