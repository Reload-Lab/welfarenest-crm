<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WnPlusOidcClient extends Model
{
    protected $fillable = [
        'name',
        'client_id',
        'client_secret',
        'redirect_uri',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
