<?php

namespace App\Services;

use App\Mail\ConsentRequestMail;
use App\Models\ConsentRequest;
use App\Models\ContactPoint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

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

    public function send(ConsentRequest $consentRequest): void
    {
        if ($consentRequest->status !== 'pending') {
            throw new RuntimeException(
                'La richiesta di consenso non è più in attesa.'
            );
        }

        if ($consentRequest->expires_at->isPast()) {
            throw new RuntimeException(
                'La richiesta di consenso è scaduta.'
            );
        }

        $consentRequest->loadMissing('contactPoint');

        $email = $consentRequest->contactPoint?->value;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'La richiesta non dispone di un indirizzo email valido.'
            );
        }

        Mail::to($email)->send(
            new ConsentRequestMail($consentRequest)
        );

        $consentRequest->update([
            'sent_at' => now(),
        ]);
    }
}