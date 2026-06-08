<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentVersion extends Model
{
    protected $fillable = [
        'consent_type_id',
        'version_code',
        'title',
        'content_text',
        'content_file_path',
        'published_at',
        'is_active',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function consentType()
    {
        return $this->belongsTo(ConsentType::class);
    }
}