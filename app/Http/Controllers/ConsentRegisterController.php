<?php

namespace App\Http\Controllers;

use App\Models\Consent;
use App\Models\ConsentRequest;
use App\Models\ConsentType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Registro dei consensi: cosa è stato concesso, negato o revocato, e quali
 * richieste sono state inviate.
 *
 * Sola lettura. La registrazione manuale e la revoca vivono altrove: qui si
 * consulta e si filtra soltanto.
 *
 * **Limite voluto**: il registro mostra solo i consensi con
 * `owner_type = 'person'`. I consensi raccolti dal portale e dall'onboarding
 * WN+ sono scritti con `owner_type = 'wn_plus_account'` e hanno un'anagrafica
 * separata, quindi non sono ricercabili per nome sulla tabella `people` e non
 * compaiono in questi elenchi. Estenderli richiede una seconda join e una
 * colonna "tipo di soggetto": rimandato, vedi claude/centro-consensi-plan.md.
 */
class ConsentRegisterController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    /**
     * Data dell'evento, che cambia colonna a seconda dello stato della riga.
     * `created_at` è l'ultima risorsa: una riga senza nessuna delle tre date
     * non dovrebbe esistere, ma se esiste è meglio ordinarla che perderla.
     */
    private const EVENT_DATE = 'COALESCE(consents.granted_at, consents.denied_at, consents.revoked_at, consents.created_at)';

    private const CONSENT_SORTS = [
        'person' => 'people.last_name',
        'status' => 'consents.status',
        'event_at' => self::EVENT_DATE,
        'created_at' => 'consents.created_at',
    ];

    private const REQUEST_SORTS = [
        'person' => 'people.last_name',
        'sent_at' => 'consent_requests.sent_at',
        'expires_at' => 'consent_requests.expires_at',
        'status' => 'consent_requests.status',
    ];

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $consentTypeId = $request->string('consent_type_id')->toString();
        $status = $request->string('status')->toString();
        $origin = $request->string('origin')->toString();

        $query = Consent::query()
            ->with(['consentType', 'consentVersion', 'createdByUser'])
            ->where('consents.owner_type', 'person')
            ->join('people', 'people.id', '=', 'consents.owner_id')
            ->select([
                'consents.*',
                'people.first_name as person_first_name',
                'people.last_name as person_last_name',
            ])
            ->when($search !== '', fn (Builder $q) => $this->applyPersonSearch($q, $search))
            ->when($consentTypeId !== '', fn (Builder $q) => $q->where('consents.consent_type_id', (int) $consentTypeId))
            ->when($status !== '', fn (Builder $q) => $q->where('consents.status', $status))
            ->when($origin === 'manual', fn (Builder $q) => $q->whereIn('consents.source', $this->manualSources()))
            // "automatico" è il complemento: include anche le righe con source
            // nullo o con un valore non più in catalogo, che non sono manuali.
            ->when($origin === 'automatic', fn (Builder $q) => $q->where(function (Builder $inner) {
                $inner->whereNotIn('consents.source', $this->manualSources())
                    ->orWhereNull('consents.source');
            }));

        if ($from = $request->date('from')) {
            $query->whereRaw(self::EVENT_DATE.' >= ?', [$from->startOfDay()]);
        }

        if ($to = $request->date('to')) {
            $query->whereRaw(self::EVENT_DATE.' <= ?', [$to->endOfDay()]);
        }

        return view('consents.index', [
            'rows' => $this->paginate($query, $request, self::CONSENT_SORTS, 'event_at', 'consents.id'),
            'consentTypes' => ConsentType::orderBy('name')->get(['id', 'name']),
            'filters' => $this->filters($request, ['search', 'consent_type_id', 'status', 'origin']),
        ]);
    }

    public function requests(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $query = ConsentRequest::query()
            ->with(['contactPoint', 'createdByUser'])
            ->where('consent_requests.owner_type', 'person')
            ->join('people', 'people.id', '=', 'consent_requests.owner_id')
            ->select([
                'consent_requests.*',
                'people.first_name as person_first_name',
                'people.last_name as person_last_name',
            ])
            ->when($search !== '', fn (Builder $q) => $this->applyPersonSearch($q, $search));

        // "Scaduta" non è uno stato a database: è una pending il cui termine
        // è passato. Tenerlo fuori dalla colonna `status` evita di dover
        // aggiornare righe con un job solo per farle invecchiare.
        $query
            ->when($status === 'expired', fn (Builder $q) => $q
                ->where('consent_requests.status', 'pending')
                ->where('consent_requests.expires_at', '<', now()))
            ->when($status === 'pending', fn (Builder $q) => $q
                ->where('consent_requests.status', 'pending')
                ->where('consent_requests.expires_at', '>=', now()))
            ->when($status === 'completed', fn (Builder $q) => $q->where('consent_requests.status', 'completed'));

        if ($from = $request->date('from')) {
            $query->where('consent_requests.sent_at', '>=', $from->startOfDay());
        }

        if ($to = $request->date('to')) {
            $query->where('consent_requests.sent_at', '<=', $to->endOfDay());
        }

        return view('consents.requests', [
            'rows' => $this->paginate($query, $request, self::REQUEST_SORTS, 'sent_at', 'consent_requests.id'),
            'filters' => $this->filters($request, ['search', 'status']),
        ]);
    }

    // --- Supporto ------------------------------------------------------------

    private function applyPersonSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $inner) use ($search) {
            $inner
                ->where('people.first_name', 'like', "%{$search}%")
                ->orWhere('people.last_name', 'like', "%{$search}%")
                ->orWhereRaw(
                    "CONCAT(COALESCE(people.first_name, ''), ' ', COALESCE(people.last_name, '')) like ?",
                    ["%{$search}%"]
                );
        });
    }

    /**
     * @return array<int, string>
     */
    private function manualSources(): array
    {
        return collect(config('consent_sources'))
            ->filter(fn (array $source) => $source['manual'] ?? false)
            ->keys()
            ->all();
    }

    /**
     * @param  array<string, string>  $allowedSorts
     */
    private function paginate(
        Builder $query,
        Request $request,
        array $allowedSorts,
        string $defaultSort,
        string $tieBreaker
    ) {
        $sort = $request->string('sort')->toString();

        if (! array_key_exists($sort, $allowedSorts)) {
            $sort = $defaultSort;
        }

        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->input('per_page', 20);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 20;
        }

        $column = $allowedSorts[$sort];

        // Le date calcolate vanno ordinate con orderByRaw; le colonne vere no,
        // così restano indicizzate.
        $sort === 'event_at'
            ? $query->orderByRaw($column.' '.$direction)
            : $query->orderBy($column, $direction);

        return $query
            ->orderBy($tieBreaker, $direction)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<int, string>  $specific
     * @return array<string, string>
     */
    private function filters(Request $request, array $specific): array
    {
        $keys = array_merge(['from', 'to', 'per_page', 'sort', 'direction'], $specific);

        return collect($keys)
            ->mapWithKeys(fn (string $key) => [$key => $request->string($key)->toString()])
            ->all();
    }
}
