<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsage extends Model
{
    use HasFactory;

    protected $table = 'contact_usages';

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

    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class);
    }
}