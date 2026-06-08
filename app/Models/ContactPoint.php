<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasConsents;

class ContactPoint extends Model
{
    use HasFactory;
    use HasConsents;

    protected $table = 'contact_points';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'contact_type_id',
        'contact_usage_id',
        'value',
        'label',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function contactType()
    {
        return $this->belongsTo(ContactType::class);
    }

    public function contactUsage()
    {
        return $this->belongsTo(ContactUsage::class);
    }

}