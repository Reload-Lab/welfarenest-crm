<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}