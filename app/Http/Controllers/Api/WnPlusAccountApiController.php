<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsentType;
use App\Models\WnPlusAccount;
use App\Services\WnPlusConsentService;
use App\Support\ActivityLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint consumati da plus.welfarenest.it. Vedi routes/api.php per la
 * distinzione fra i due e il perche' della minimizzazione lato CRM.
 */
class WnPlusAccountApiController extends Controller
{
    private const PER_PAGE_DEFAULT = 100;

    private const PER_PAGE_MAX = 250;

    public function __construct(private WnPlusConsentService $wnPlusConsents) {}

    /**
     * Sincronizzazione degli account: dati completi e stato di tutti i consensi.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:active,pending,suspended,disabled,all'],
            'updated_since' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $accounts = $this->baseQuery()
            ->when(
                ($validated['status'] ?? 'active') !== 'all',
                fn (Builder $query) => $query->where('status', $validated['status'] ?? 'active')
            )
            ->when(
                isset($validated['updated_since']),
                fn (Builder $query) => $this->changedSince($query, $validated['updated_since'])
            )
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? self::PER_PAGE_DEFAULT);

        $consents = $this->wnPlusConsents->latestForAccounts(
            $accounts->pluck('id')->all()
        );

        $data = $accounts->getCollection()->map(fn (WnPlusAccount $account) => [
            'uuid' => $account->uuid,
            'first_name' => $account->first_name,
            'last_name' => $account->last_name,
            'email' => $account->email,
            'phone' => $this->phoneFor($account),
            'account_type' => $account->account_type,
            'status' => $account->status,
            'role' => $account->role?->name,
            'level' => $account->level?->name,
            'organization' => $account->organization ? [
                'id' => $account->organization->id,
                'name' => $account->organization->name ?? $account->organization->legal_name,
            ] : null,
            'consents' => $consents[$account->id] ?? [],
            'updated_at' => $account->updated_at?->toAtomString(),
        ])->all();

        return $this->respond($request, 'accounts', $data, $accounts);
    }

    /**
     * Elenco dei membri pubblicabile nella community: gia' filtrato sui consensi
     * di visibilita', con i campi non autorizzati rimossi invece che oscurati.
     */
    public function directory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:' . self::PER_PAGE_MAX],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        // Solo account attivi: un account sospeso o disattivato non compare nella
        // community anche se a suo tempo aveva acconsentito.
        //
        // Il filtro sulla visibilita' sta nella query e non solo nella mappatura
        // qui sotto, altrimenti "total" conterebbe anche chi non compare e la
        // paginazione restituirebbe pagine mezze vuote.
        $accounts = $this->baseQuery()
            ->where('status', 'active')
            ->whereHas('consents', fn ($query) => $this->scopeLatestGranted(
                $query,
                ConsentType::PROFILE_VISIBILITY_BASIC
            ))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($validated['per_page'] ?? self::PER_PAGE_DEFAULT);

        $consents = $this->wnPlusConsents->latestForAccounts(
            $accounts->pluck('id')->all()
        );

        $data = $accounts->getCollection()
            ->map(function (WnPlusAccount $account) use ($consents) {
                $accountConsents = $consents[$account->id] ?? [];

                // Senza visibilita' del profilo base la persona non compare affatto:
                // e' il consenso che regge tutta la scheda.
                if (! $this->wnPlusConsents->isVisible($accountConsents, ConsentType::PROFILE_VISIBILITY_BASIC)) {
                    return null;
                }

                $entry = [
                    'uuid' => $account->uuid,
                    'first_name' => $account->first_name,
                    'last_name' => $account->last_name,
                    'organization' => $account->organization?->name
                        ?? $account->organization?->legal_name,
                    'account_type' => $account->account_type,
                ];

                if ($this->wnPlusConsents->isVisible($accountConsents, ConsentType::PROFILE_VISIBILITY_EMAIL)) {
                    $entry['email'] = $account->email;
                }

                if ($this->wnPlusConsents->isVisible($accountConsents, ConsentType::PROFILE_VISIBILITY_PHONE)
                    && ($phone = $this->phoneFor($account)) !== null) {
                    $entry['phone'] = $phone;
                }

                // La fotografia del profilo non esiste ancora come campo: finche'
                // non si decide dove vive, esponiamo solo il permesso, cosi' il
                // sito puo' gia' prepararne la resa.
                $entry['photo_allowed'] = $this->wnPlusConsents
                    ->isVisible($accountConsents, ConsentType::PROFILE_VISIBILITY_PHOTO);

                return $entry;
            })
            ->filter()
            ->values()
            ->all();

        return $this->respond($request, 'directory', $data, $accounts);
    }

    /**
     * Vincola la sottoquery all'ULTIMO consenso registrato per quel tipo, e
     * richiede che sia 'granted'. I consensi sono eventi: senza il confronto con
     * il massimo id, un vecchio "granted" poi revocato basterebbe a far comparire
     * la persona nella directory.
     */
    private function scopeLatestGranted(Builder $query, string $consentTypeCode): Builder
    {
        return $query
            ->where('status', 'granted')
            ->whereHas('consentType', fn (Builder $type) => $type->where('code', $consentTypeCode))
            ->whereRaw('consents.id = (
                select max(latest.id) from consents as latest
                where latest.owner_type = consents.owner_type
                  and latest.owner_id = consents.owner_id
                  and latest.consent_type_id = consents.consent_type_id
            )');
    }

    private function baseQuery(): Builder
    {
        return WnPlusAccount::query()
            ->with([
                'organization:id,name,legal_name',
                'role:id,name',
                'level:id,name',
                'person.contactPoints.contactType',
            ]);
    }

    /**
     * Filtro incrementale. Non basta wn_plus_accounts.updated_at: cambiare un
     * consenso inserisce una riga in consents e non tocca l'account, quindi un
     * delta basato sul solo account perderebbe proprio le variazioni di consenso,
     * che sono il motivo per cui il sito richiama l'endpoint.
     */
    private function changedSince(Builder $query, string $since): Builder
    {
        return $query->where(function (Builder $q) use ($since) {
            $q->where('updated_at', '>=', $since)
                ->orWhereExists(function ($sub) use ($since) {
                    $sub->selectRaw('1')
                        ->from('consents')
                        ->whereColumn('consents.owner_id', 'wn_plus_accounts.id')
                        ->where('consents.owner_type', 'wn_plus_account')
                        ->where('consents.created_at', '>=', $since);
                });
        });
    }

    /**
     * Il telefono non sta su wn_plus_accounts: vive fra i recapiti della persona
     * collegata, che pero' e' facoltativa. Nessuna persona o nessun recapito
     * telefonico significa semplicemente nessun telefono da esporre.
     */
    private function phoneFor(WnPlusAccount $account): ?string
    {
        return $account->person?->contactPoints
            ->where('is_active', true)
            ->first(fn ($point) => $point->contactType?->category === 'phone')
            ?->value;
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function respond(Request $request, string $endpoint, array $data, LengthAwarePaginator $paginator): JsonResponse
    {
        // L'accesso agli endpoint e' un trasferimento di dati personali verso un
        // altro sistema: va tracciato come tutto il resto, per poterlo documentare.
        ActivityLogger::log(ActivityLogger::WN_PLUS_API_ACCESSED, null, [
            'endpoint' => $endpoint,
            'ip' => $request->ip(),
            'returned' => count($data),
            'page' => $paginator->currentPage(),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'generated_at' => now()->toAtomString(),
            ],
        ]);
    }
}
