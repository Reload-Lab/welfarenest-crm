<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUsage extends Model
{
    use HasFactory;

    /**
     * Uso che marca il recapito di riferimento di un soggetto. Dal 22/09/2026
     * sostituisce il flag is_primary come modo di indicare il recapito
     * principale: vedi Person::primaryOrFirstEmailContactPoint().
     */
    public const MAIN = 'main';

    protected $table = 'contact_usages';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class);
    }
}