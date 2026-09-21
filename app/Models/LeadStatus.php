<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadStatus extends Model
{
    use HasFactory;

    protected $table = 'lead_statuses';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_won',
        'is_lost',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Stato terminale: il lead non è più in lavorazione, con qualunque esito.
     */
    public function getIsFinalAttribute(): bool
    {
        return $this->is_won || $this->is_lost;
    }

    public function scopeOpen($query)
    {
        return $query->where('is_won', false)->where('is_lost', false);
    }
}
