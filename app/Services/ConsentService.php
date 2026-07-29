<?php

namespace App\Services;

use App\Models\Consent;
use App\Models\ConsentType;

class ConsentService
{
    public function grant(string $ownerType, int $ownerId, string $consentTypeCode, string $source): ?Consent
    {
        return $this->store($ownerType, $ownerId, $consentTypeCode, 'granted', $source);
    }

    public function deny(string $ownerType, int $ownerId, string $consentTypeCode, string $source): ?Consent
    {
        return $this->store($ownerType, $ownerId, $consentTypeCode, 'denied', $source);
    }

    public function latest(string $ownerType, int $ownerId, string $consentTypeCode): ?Consent
    {
        $consentType = ConsentType::where('code', $consentTypeCode)->first();

        if (! $consentType) {
            return null;
        }

        return Consent::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('consent_type_id', $consentType->id)
            ->latest()
            ->first();
    }

    public function hasGranted(string $ownerType, int $ownerId, string $consentTypeCode): bool
    {
        return $this->latest($ownerType, $ownerId, $consentTypeCode)?->status === 'granted';
    }

    private function store(
        string $ownerType,
        int $ownerId,
        string $consentTypeCode,
        string $status,
        string $source
    ): ?Consent {
        $consentType = ConsentType::where('code', $consentTypeCode)->first();

        if (! $consentType) {
            return null;
        }

        $version = $consentType->versions()
            ->where('is_active', true)
            ->latest('published_at')
            ->first();

        return Consent::create([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $version?->id,
            'status' => $status,
            'requested_at' => null,
            'granted_at' => $status === 'granted' ? now() : null,
            'denied_at' => $status === 'denied' ? now() : null,
            'revoked_at' => null,
            'source' => $source,
            'created_by_user_id' => auth()->id(),
        ]);
    }

    public function updateConsents(Request $request, ConsentService $consentService)
    {
        $account = $request->attributes->get('wnPlusAccount');

        foreach (['promotional_emails', 'image_disclosure'] as $code) {
            if ($request->boolean($code)) {
                $consentService->grant('wn_plus_account', $account->id, $code, 'wn_plus_portal_self_service');
            } else {
                $consentService->deny('wn_plus_account', $account->id, $code, 'wn_plus_portal_self_service');
            }
        }

        return back()->with('success', 'Consensi aggiornati correttamente.');
    }


}