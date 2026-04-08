<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrganizationType;


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

}

