<?php

namespace App\Services;

use App\Models\Consent;
use App\Models\ConsentType;
use App\Support\ActivityLogger;

class ConsentService
{
    public function grant(string $ownerType, int $ownerId, string $consentTypeCode, string $source, ?string $versionCode = null): ?Consent
    {
        return $this->store($ownerType, $ownerId, $consentTypeCode, 'granted', $source, $versionCode);
    }

    public function deny(string $ownerType, int $ownerId, string $consentTypeCode, string $source, ?string $versionCode = null): ?Consent
    {
        return $this->store($ownerType, $ownerId, $consentTypeCode, 'denied', $source, $versionCode);
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
        string $source,
        ?string $versionCode = null
    ): ?Consent {
        $consentType = ConsentType::where('code', $consentTypeCode)->first();

        if (! $consentType) {
            return null;
        }

        $versionQuery = $consentType->versions()->where('is_active', true);

        if ($versionCode !== null) {
            // Versione esplicita richiesta (es. audience-specific per ruolo):
            // se non la troviamo attiva con questo codice, meglio null che una versione sbagliata.
            $versionQuery->where('version_code', $versionCode);
        }

        $version = $versionQuery->latest('published_at')->first();

        $consent = Consent::create([
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

        ActivityLogger::log(
            $status === 'granted' ? ActivityLogger::CONSENT_GRANTED : ActivityLogger::CONSENT_DENIED,
            $consent,
            [
                'consent_type_code' => $consentTypeCode,
                'version_code' => $version?->version_code,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'source' => $source,
            ],
        );

        return $consent;
    }
}
