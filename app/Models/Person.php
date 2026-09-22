<?php

namespace App\Models;

use App\Models\ContactPoint;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasConsents;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;
    use HasConsents;

    /**
     * Stati sintetici del consenso privacy usati dalla UI (scheda persona).
     * Non sono valori persistiti: sono la combinazione tra i Consent registrati
     * e le richieste di consenso inviate — vedi consentRequestState().
     */
    public const CONSENT_STATE_GRANTED = 'granted';
    public const CONSENT_STATE_AWAITING = 'awaiting';
    public const CONSENT_STATE_REFUSED = 'refused';
    public const CONSENT_STATE_EXPIRED = 'expired';
    public const CONSENT_STATE_NEVER_SENT = 'never_sent';

    protected $table = 'people';

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    public function organizationRelations()
    {
        return $this->hasMany(PersonOrganizationRelation::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->last_name,
        ])->filter()->implode(' '));
    }

    public function getDisplayNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : null;
    }

    public function contactPoints()
    {
        return $this->hasMany(ContactPoint::class, 'owner_id')
            ->where('owner_type', 'person');
    }

    /**
     * Email di riferimento per l'invio della richiesta di consenso: la email
     * primaria se impostata, altrimenti la prima email disponibile (per id).
     * Su questo indirizzo, e solo su questo, viene inviata la richiesta di
     * consenso/informativa alla persona — semplificazione richiesta dalla DPO
     * per evitare più richieste parallele sulla stessa persona.
     */
    public function primaryOrFirstEmailContactPoint(): ?ContactPoint
    {
        return $this->contactPoints()
            ->whereHas('contactType', fn ($query) => $query->where('category', 'email'))
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();
    }

    public function wnPlusAccounts(): HasMany
    {
        return $this->hasMany(WnPlusAccount::class);
    }

    /**
     * Richieste di consenso intestate a questa persona. Come per i recapiti,
     * owner_type è una stringa controllata ('person'), non una FQCN.
     */
    public function consentRequests(): HasMany
    {
        return $this->hasMany(ConsentRequest::class, 'owner_id')
            ->where('owner_type', 'person');
    }

    /**
     * Ultima richiesta inviata, a prescindere dallo stato: serve a distinguere
     * "non abbiamo mai chiesto nulla" da "abbiamo chiesto e non ha risposto".
     */
    public function latestConsentRequest(): ?ConsentRequest
    {
        return $this->consentRequests->sortByDesc('id')->first();
    }

    /**
     * Richiesta ancora aperta: 'pending' e non scaduta. Una richiesta 'pending'
     * ma scaduta non è più sollecitabile — il reinvio ne genera una nuova.
     */
    public function pendingConsentRequest(): ?ConsentRequest
    {
        return $this->consentRequests
            ->sortByDesc('id')
            ->first(fn (ConsentRequest $request) => $request->status === 'pending'
                && $request->expires_at?->isFuture());
    }

    /**
     * Stato complessivo del consenso privacy ai fini della UI: unisce ciò che
     * dicono i Consent registrati e ciò che dicono le richieste inviate. Serve
     * perché "consenso mai chiesto" e "richiesta inviata e mai riscontrata"
     * guardando i soli Consent sarebbero indistinguibili — in entrambi i casi
     * non esiste alcuna riga Consent, perché la riga nasce solo alla risposta.
     */
    public function consentRequestState(string $code = ConsentType::PRIVACY_NOTICE): string
    {
        if ($this->pendingConsentRequest()) {
            return self::CONSENT_STATE_AWAITING;
        }

        $status = $this->latestConsentByCode($code)?->status;

        if ($status === 'granted') {
            return self::CONSENT_STATE_GRANTED;
        }

        if (in_array($status, ['denied', 'revoked'], true)) {
            return self::CONSENT_STATE_REFUSED;
        }

        return $this->latestConsentRequest()
            ? self::CONSENT_STATE_EXPIRED
            : self::CONSENT_STATE_NEVER_SENT;
    }

    public function consentRequestStateVariant(string $code = ConsentType::PRIVACY_NOTICE): string
    {
        return match ($this->consentRequestState($code)) {
            self::CONSENT_STATE_GRANTED => 'success',
            self::CONSENT_STATE_AWAITING => 'pending',
            self::CONSENT_STATE_REFUSED => 'danger',
            default => 'warning',
        };
    }

    public function consentRequestStateLabel(string $code = ConsentType::PRIVACY_NOTICE): string
    {
        return match ($this->consentRequestState($code)) {
            self::CONSENT_STATE_GRANTED => 'Consenso acquisito',
            self::CONSENT_STATE_AWAITING => 'Richiesta inviata, in attesa di risposta',
            self::CONSENT_STATE_REFUSED => 'Consenso negato o revocato',
            self::CONSENT_STATE_EXPIRED => 'Richiesta scaduta senza risposta',
            default => 'Consenso mancante',
        };
    }
}
