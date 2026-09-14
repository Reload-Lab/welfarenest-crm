<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentRequestItem extends Model
{
    protected $fillable = [
        'consent_request_id',
        'consent_type_id',
        'consent_version_id',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function consentRequest(): BelongsTo
    {
        return $this->belongsTo(ConsentRequest::class);
    }

    public function consentType(): BelongsTo
    {
        return $this->belongsTo(ConsentType::class);
    }

    public function consentVersion(): BelongsTo
    {
        return $this->belongsTo(ConsentVersion::class);
    }
}
