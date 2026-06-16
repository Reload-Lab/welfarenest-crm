<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WnPlusOidcAuthCode extends Model
{
    protected $fillable = [
        'wn_plus_oidc_client_id',
        'wn_plus_account_id',
        'code',
        'redirect_uri',
        'scope',
        'nonce',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
