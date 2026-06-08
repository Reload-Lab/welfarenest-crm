<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}