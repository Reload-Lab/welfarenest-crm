<?php

namespace App\Services;

use App\Models\ConsentRequest;
use App\Models\ContactPoint;
use Illuminate\Support\Str;

class ConsentRequestService
{
    public function createForContactPoint(ContactPoint $contactPoint): ConsentRequest
    {
        return ConsentRequest::create([
            'token' => Str::random(64),
            'owner_type' => $contactPoint->owner_type,
            'owner_id' => $contactPoint->owner_id,
            'contact_point_id' => $contactPoint->id,
            'created_by_user_id' => auth()->id(),
            'expires_at' => now()->addDays(7),
            'sent_at' => null,
            'completed_at' => null,
            'status' => 'pending',
            'source' => 'email_request',
        ]);
    }
}