<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WnPlusAccount extends Model
{
    protected $fillable = [
        'uuid',
        'organization_id',
        'person_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'wn_plus_role_id',
        'wn_plus_level_id',
        'status',
        'max_users',
        'invited_by_account_id',
        'created_by_user_id',
        'email_verified_at',
        'last_login_at',
        'account_type',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(WnPlusRole::class, 'wn_plus_role_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(WnPlusLevel::class, 'wn_plus_level_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WnPlusInvitation::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(WnPlusAccount::class, 'invited_by_account_id');
    }

    public function invitedAccounts(): HasMany
    {
        return $this->hasMany(WnPlusAccount::class, 'invited_by_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getManagedUsersCountAttribute(): int
    {
        return $this->invitedAccounts()
            ->where('account_type', 'user')
            ->count();
    }

    public function getAvailableSlotsAttribute(): int
    {
        return max(
            0,
            ($this->max_users ?? 0) - $this->managed_users_count
        );
    }


}