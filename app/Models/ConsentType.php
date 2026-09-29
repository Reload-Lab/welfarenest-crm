<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ConsentType extends Model
{

    public const PRIVACY_NOTICE = 'privacy_notice';

    public const PROMOTIONAL_EMAILS = 'promotional_emails';

    public const IMAGE_DISCLOSURE = 'image_disclosure';

    // Consensi gestiti dall'utente nell'area riservata WN+ (informative 12 e 13).
    // I quattro profile_visibility_* governano cosa la community può vedere del profilo:
    // email e telefono sono subordinati a profile_visibility_basic (vedi informativa).
    public const PROFILE_VISIBILITY_BASIC = 'profile_visibility_basic';

    public const PROFILE_VISIBILITY_EMAIL = 'profile_visibility_email';

    public const PROFILE_VISIBILITY_PHONE = 'profile_visibility_phone';

    public const PROFILE_VISIBILITY_PHOTO = 'profile_visibility_photo';

    public const SERVICE_UPDATES = 'service_updates';

    public const IDENTIFIABLE_SURVEYS = 'identifiable_surveys';

    /**
     * I consensi che referente e membro WN+ gestiscono autonomamente nel portale,
     * nell'ordine in cui compaiono nell'informativa.
     */
    public const WN_PLUS_SELF_MANAGED = [
        self::PROFILE_VISIBILITY_BASIC,
        self::PROFILE_VISIBILITY_EMAIL,
        self::PROFILE_VISIBILITY_PHONE,
        self::PROFILE_VISIBILITY_PHOTO,
        self::SERVICE_UPDATES,
        self::IMAGE_DISCLOSURE,
        self::IDENTIFIABLE_SURVEYS,
    ];

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
