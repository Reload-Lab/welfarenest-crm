<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentRequest extends Model
{
    protected $fillable = [
        'token',
        'owner_type',
        'owner_id',
        'contact_point_id',
        'created_by_user_id',
        'expires_at',
        'sent_at',
        'completed_at',
        'status',
        'source',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function contactPoint()
    {
        return $this->belongsTo(ContactPoint::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsentRequestItem::class)->orderBy('sort_order');
    }

    /**
     * "Scaduta" non è uno stato a database ma una pending il cui termine è
     * passato: così nessun job deve aggiornare righe solo per farle invecchiare.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'pending' && (bool) $this->expires_at?->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'completed') {
            return 'Completata';
        }

        return $this->is_expired ? 'Scaduta' : 'In attesa';
    }

    public function getStatusVariantAttribute(): string
    {
        if ($this->status === 'completed') {
            return 'success';
        }

        return $this->is_expired ? 'muted' : 'info';
    }
}
