<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WnPlusInvitation extends Model
{
    protected $fillable = [
        'wn_plus_account_id',
        'token',
        'expires_at',
        'sent_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(WnPlusAccount::class, 'wn_plus_account_id');
    }
}