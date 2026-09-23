<?php

namespace App\Services;

use App\Mail\ConsentRequestMail;
use App\Models\ConsentRequest;
use App\Models\ConsentType;
use App\Models\ContactPoint;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class ConsentRequestService
{
    /**
     * Crea una richiesta di consenso per un recapito, con gli item (consent_type +
     * versione) da proporre al destinatario nel form pubblico.
     *
     * $items, se passato, è un array di:
     *   ['consent_type_code' => string, 'version_code' => ?string, 'is_required' => bool, 'sort_order' => int]
     *
     * Se omesso, viene usato defaultItemsForContactPoint(): la coppia
     * privacy_notice (obbligatoria) + promotional_emails (facoltativa),
     * con le versioni "lead/primo contatto" — la coppia più generica,
     * pensata per un recapito appena raccolto senza altro contesto
     * (nessuna relazione con un'organizzazione, nessun ruolo noto).
     * Un chiamante con più contesto (es. una futura dashboard Consensi che
     * compone la richiesta a mano) può passare un set diverso esplicitamente.
     */
    public function createForContactPoint(ContactPoint $contactPoint, ?array $items = null): ConsentRequest
    {
        $consentRequest = ConsentRequest::create([
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

        $this->attachItems($consentRequest, $items ?? $this->defaultItemsForContactPoint());

        return $consentRequest;
    }

    /**
     * @param array<int, array{consent_type_code: string, version_code?: ?string, is_required?: bool, sort_order?: int}> $items
     */
    private function attachItems(ConsentRequest $consentRequest, array $items): void
    {
        foreach ($items as $item) {
            $consentType = ConsentType::where('code', $item['consent_type_code'])->first();

            if (! $consentType) {
                continue;
            }

            $versionQuery = $consentType->versions()->where('is_active', true);

            if (! empty($item['version_code'])) {
                $versionQuery->where('version_code', $item['version_code']);
            }

            $version = $versionQuery->latest('published_at')->first();

            $consentRequest->items()->create([
                'consent_type_id' => $consentType->id,
                'consent_version_id' => $version?->id,
                'is_required' => $item['is_required'] ?? false,
                'sort_order' => $item['sort_order'] ?? 0,
            ]);
        }
    }

    private function defaultItemsForContactPoint(): array
    {
        return [
            [
                'consent_type_code' => ConsentType::PRIVACY_NOTICE,
                'version_code' => '01_lead_2026_v1',
                'is_required' => true,
                'sort_order' => 10,
            ],
            [
                'consent_type_code' => ConsentType::PROMOTIONAL_EMAILS,
                'version_code' => '02_newsletter_2026_v1',
                'is_required' => false,
                'sort_order' => 20,
            ],
        ];
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

        ActivityLogger::log(ActivityLogger::CONSENT_REQUEST_SENT, $consentRequest, [
            'email' => $email,
            'owner_type' => $consentRequest->owner_type,
            'owner_id' => $consentRequest->owner_id,
            'contact_point_id' => $consentRequest->contact_point_id,
            'expires_at' => $consentRequest->expires_at?->toDateTimeString(),
        ]);
    }
}
