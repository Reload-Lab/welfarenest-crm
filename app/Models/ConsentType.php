<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ConsentType extends Model
{

    public const PRIVACY_NOTICE = 'privacy_notice';

    public const PROMOTIONAL_EMAILS = 'promotional_emails';

    public const IMAGE_DISCLOSURE = 'image_disclosure';

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'is_active',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(ConsentVersion::class);
    }
}