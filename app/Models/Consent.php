<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    protected $fillable = [

        'owner_type',
        'owner_id',

        'consent_type_id',
        'consent_version_id',

        'status',

        'requested_at',
        'granted_at',
        'revoked_at',
        'denied_at',

        'source',

        'created_by_user_id',

        'notes',
        'evidence_file_path',
    ];

    protected $casts = [

        'requested_at' => 'datetime',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'denied_at' => 'datetime',

    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function consentType()
    {
        return $this->belongsTo(ConsentType::class);
    }

    public function consentVersion()
    {
        return $this->belongsTo(ConsentVersion::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class);
    }
}