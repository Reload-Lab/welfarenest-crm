<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'address_type_id',
        'label',
        'street',
        'street_number',
        'postal_code',
        'city',
        'province',
        'region',
        'country',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function addressType()
    {
        return $this->belongsTo(AddressType::class);
    }
}