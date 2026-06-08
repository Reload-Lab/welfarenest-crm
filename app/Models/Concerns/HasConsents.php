<?php

namespace App\Models\Concerns;

use App\Models\Consent;

trait HasConsents
{
    public function consents()
    {
        return $this->morphMany(Consent::class, 'owner');
    }

    public function latestConsentByCode(string $code)
    {
        return $this->consents
            ->where('consentType.code', $code)
            ->sortByDesc('created_at')
            ->first();
    }

    public function consentBadgeVariant(string $code): string
    {
        $consent = $this->latestConsentByCode($code);

        if (! $consent) {
            return 'warning';
        }

        return match ($consent->status) {
            'granted' => 'success',
            'pending' => 'pending',
            'denied', 'revoked' => 'danger',
            default => 'muted',
        };
    }

    public function consentStatusLabel(string $code): string
    {
        $consent = $this->latestConsentByCode($code);

        if (! $consent) {
            return 'Consenso mancante';
        }

        return match ($consent->status) {
            'granted' => 'Consenso acquisito',
            'pending' => 'Richiesta consenso inviata',
            'denied' => 'Consenso negato',
            'revoked' => 'Consenso revocato',
            default => 'Stato consenso non disponibile',
        };
    }
}