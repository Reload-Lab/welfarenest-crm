<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WnPlusAccount;
use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\WnPlusLevel;
use App\Models\WnPlusRole;
use App\Models\Consent;
use App\Services\WnPlusInvitationService;
use App\Models\ConsentRequest;

use Illuminate\Support\Str;


class WnPlusAccountController extends Controller
{
    public function index(Request $request)
    {
        // L'elenco parte dall'organizzazione: ogni organizzazione è un gruppo che
        // contiene i propri referenti (account_type=manager) e, annidati sotto a
        // ciascuno, gli utenti che ha invitato (account_type=user,
        // invited_by_account_id = referente). Prima il raggruppamento era per
        // referente e l'organizzazione era una colonna: il cliente però ragiona nel
        // senso opposto — sceglie l'organizzazione e poi le assegna un referente.
        //
        // Nota: il raggruppamento è solo di presentazione. Nessun dato cambia e gli
        // account già inseriti continuano a essere letti da organization_id e
        // invited_by_account_id esattamente come prima.

        // 'search' è il nome usato dalle altre sezioni del CRM; 'q' resta accettato
        // perché è il parametro che la pagina usava finora (link e preferiti salvati).
        $search = trim((string) ($request->input('search') ?? $request->input('q') ?? ''));

        $sort = (string) $request->input('sort', 'organization');

        if (! in_array($sort, ['organization', 'name', 'accounts_count'], true)) {
            $sort = 'organization';
        }

        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $request->input('per_page', 20);

        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 20;
        }

        // L'intestazione "Organizzazione" ordina i gruppi, "Utente" ordina le persone
        // dentro ogni gruppo: in una tabella annidata sono due ordinamenti distinti.
        $accountsDirection = $sort === 'name' ? $direction : 'asc';

        $matchesAccount = function ($query) use ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        };

        $withAccounts = function ($query) use ($accountsDirection) {
            $query->with(['role', 'level', 'consents.consentType'])
                ->orderBy('last_name', $accountsDirection)
                ->orderBy('first_name', $accountsDirection);
        };

        $organizationsQuery = Organization::query()
            ->whereHas('wnPlusAccounts')
            ->withCount('wnPlusAccounts')
            ->with(['wnPlusAccounts' => $withAccounts]);

        // Un'organizzazione resta in elenco sia se corrisponde lei, sia se corrisponde
        // una delle persone che contiene: così cercando un nome si vede anche dove sta.
        if ($search !== '') {
            $organizationsQuery->where(function ($query) use ($matchesAccount, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('legal_name', 'like', "%{$search}%")
                    ->orWhereHas('wnPlusAccounts', $matchesAccount);
            });
        }

        if ($sort === 'accounts_count') {
            $organizationsQuery->orderBy('wn_plus_accounts_count', $direction);
        } else {
            $organizationsQuery->orderBy('name', $sort === 'organization' ? $direction : 'asc');
        }

        $organizations = $organizationsQuery
            ->paginate($perPage)
            ->withQueryString();

        $groups = $organizations
            ->getCollection()
            ->map(fn (Organization $organization) => $this->buildAccountGroup(
                $organization,
                $organization->wnPlusAccounts
            ));

        // Account senza organizzazione collegata: dato anomalo, non dovrebbe
        // succedere nel flusso normale, ma non vanno persi dall'elenco.
        $unassignedQuery = WnPlusAccount::query()
            ->whereNull('organization_id')
            ->with(['role', 'level', 'consents.consentType']);

        if ($search !== '') {
            $unassignedQuery->where($matchesAccount);
        }

        $unassignedAccounts = $unassignedQuery
            ->orderBy('last_name', $accountsDirection)
            ->orderBy('first_name', $accountsDirection)
            ->get();

        $unassignedGroup = $unassignedAccounts->isNotEmpty()
            ? $this->buildAccountGroup(null, $unassignedAccounts)
            : null;

        return view('wn-plus.accounts.index', compact(
            'organizations',
            'groups',
            'unassignedGroup',
            'search',
            'sort',
            'direction',
            'perPage'
        ));
    }

    /**
     * Costruisce l'albero di un gruppo: i referenti dell'organizzazione e, sotto a
     * ciascuno, gli utenti che ha invitato.
     *
     * Gli utenti il cui referente non sta in questo gruppo (invited_by_account_id
     * nullo, oppure un referente di un'altra organizzazione) finiscono in
     * looseUsers: restano visibili sotto la loro organizzazione invece di sparire.
     * Ogni account compare una volta sola, nel gruppo della propria organizzazione.
     */
    private function buildAccountGroup(?Organization $organization, $accounts): array
    {
        $managers = $accounts->where('account_type', 'manager')->values();
        $managerIds = $managers->pluck('id')->all();

        $users = $accounts->where('account_type', '!=', 'manager');

        $managerRows = $managers->map(fn (WnPlusAccount $manager) => [
            'manager' => $manager,
            'users' => $users
                ->where('invited_by_account_id', $manager->id)
                ->values(),
        ])->all();

        $looseUsers = $users
            ->filter(fn (WnPlusAccount $user) => ! in_array($user->invited_by_account_id, $managerIds, true))
            ->values();

        return [
            'organization' => $organization,
            'managers' => $managerRows,
            'looseUsers' => $looseUsers,
            'accountsCount' => $accounts->count(),
            'managersCount' => $managers->count(),
            'usersCount' => $users->count(),
            'key' => $organization ? 'org-' . $organization->id : 'org-none',
        ];
    }

    public function create()
    {
        $organizations = Organization::query()
            ->with([
                'personRelations.person',
                'personRelations.person.contactPoints.contactType',
                'personRelations.contactPoints.contactType',
                'personRelations.qualification',
                'personRelations.department',
            ])
            ->orderBy('name')
            ->get();

        $roles = WnPlusRole::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $levels = WnPlusLevel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('wn-plus.accounts.create', compact(
            'organizations',
            'roles',
            'levels'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'person_id' => ['required', 'exists:people,id'],
            'email' => ['required', 'email', 'unique:wn_plus_accounts,email'],
            'wn_plus_role_id' => ['required', 'exists:wn_plus_roles,id'],
            'wn_plus_level_id' => ['required', 'exists:wn_plus_levels,id'],
        ]);

        $relationExists = PersonOrganizationRelation::query()
            ->where('organization_id', $validated['organization_id'])
            ->where('person_id', $validated['person_id'])
            ->exists();

        if (! $relationExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'person_id' => 'La persona selezionata non risulta collegata all’organizzazione scelta.',
                ]);
        }

        $alreadyExists = WnPlusAccount::query()
            ->where('organization_id', $validated['organization_id'])
            ->where('person_id', $validated['person_id'])
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'person_id' => 'Esiste già un account WN+ per questa persona e questa organizzazione.',
                ]);
        }

        $person = Person::findOrFail($validated['person_id']);

        // account_type segue il ruolo scelto: prima era 'manager' fisso, quindi
        // creando un account con ruolo "Utente" usciva comunque un referente.
        $accountType = WnPlusAccount::accountTypeForRole($validated['wn_plus_role_id']);

        WnPlusAccount::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $validated['organization_id'],
            'person_id' => $person->id,
            'first_name' => $person->first_name,
            'last_name' => $person->last_name,
            'email' => $validated['email'],
            'wn_plus_role_id' => $validated['wn_plus_role_id'],
            'wn_plus_level_id' => $validated['wn_plus_level_id'],
            'status' => 'invited',
            'account_type' => $accountType,
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('wn-plus.accounts.index')
            ->with('success', $accountType === 'manager'
                ? 'Referente WN+ creato correttamente.'
                : 'Utente WN+ creato correttamente.');
    }


    public function edit(WnPlusAccount $account)
    {
        $roles = WnPlusRole::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $levels = WnPlusLevel::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('wn-plus.accounts.edit', compact('account', 'roles', 'levels'));
    }   


    public function show(WnPlusAccount $account)
    {
        $account->load([
            'organization',
            'person',
            'role',
            'level',
            'invitations',
            'invitedAccounts.organization',
            'invitedAccounts.role',
            'invitedAccounts.level',
            'invitedAccounts.invitations',
            'invitedAccounts.consents.consentType',
            'invitedAccounts.consents.consentVersion',
        ]);

        return view('wn-plus.accounts.show', compact('account'));
    }


    public function update(Request $request, WnPlusAccount $account)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:wn_plus_accounts,email,' . $account->id],
            'wn_plus_role_id' => ['required', 'exists:wn_plus_roles,id'],
            'wn_plus_level_id' => ['required', 'exists:wn_plus_levels,id'],
            'status' => ['required', 'in:invited,active,suspended,disabled'],
        ]);

        // Il menu "Ruolo" cambiava solo wn_plus_role_id, mentre account_type —
        // quello che decide accesso all'area referente, permesso di invitare e
        // posizione in elenco — restava com'era. Risultato: un utente promosso a
        // referente restava appeso al suo vecchio referente e non entrava nella
        // propria area riservata.
        $newAccountType = WnPlusAccount::accountTypeForRole($validated['wn_plus_role_id']);
        $wasManager = $account->account_type === 'manager';

        // Retrocessione a utente semplice: stessa regola di sospendi, disabilita
        // ed elimina. Senza questo blocco i suoi utenti resterebbero con
        // invited_by_account_id puntato a lui, ma lui non avrebbe più accesso
        // all'area referente: account che nessuno può più gestire.
        if ($wasManager && $newAccountType === 'user' && $account->hasActiveInvitedAccounts()) {
            return back()
                ->withInput()
                ->withErrors([
                    'wn_plus_role_id' => 'Impossibile riportare questo referente a utente semplice: ha utenti invitati non disattivati. Disattiva o riassegna prima gli utenti collegati.',
                ]);
        }

        $validated['account_type'] = $newAccountType;

        // Promozione a referente: un referente non sta sotto nessuno. Chi lo aveva
        // invitato resta scritto nell'activity log, non serve tenerlo qui.
        if ($newAccountType === 'manager') {
            $validated['invited_by_account_id'] = null;
        }

        $account->update($validated);

        return redirect()
            ->route('wn-plus.accounts.show', $account)
            ->with('success', 'Account WN+ aggiornato correttamente.');
    }

    public function createUser(WnPlusAccount $account)
    {
        $account->load(['organization', 'role', 'level', 'invitedAccounts']);

        if ($account->account_type !== 'manager') {
            abort(404);
        }

        return view('wn-plus.accounts.users.create', compact('account'));
    }

    public function storeUser(Request $request, WnPlusAccount $account, WnPlusInvitationService $invitations)
    {
        $account->load(['level']);

        if ($account->account_type !== 'manager') {
            abort(404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:wn_plus_accounts,email'],
        ]);

        // Stesse regole usate dal referente quando invita dalla propria area riservata.
        $invitations->createMember($account, $validated, auth()->id());

        return redirect()
            ->route('wn-plus.accounts.show', $account)
            ->with('success', 'Utente WN+ creato correttamente.');
    }


    public function sendInvitation(WnPlusAccount $account, WnPlusInvitationService $invitations)
    {
        if ($account->status === 'active') {
            return back()->with('error', 'Questo account è già attivo.');
        }

        $invitations->send($account, 'crm');

        return back()->with('success', 'Invito inviato correttamente a ' . $account->email . '.');
    }

    public function destroy(WnPlusAccount $account)
    {
        if ($account->hasActiveInvitedAccounts()) {
            return back()->with('error', 'Impossibile eliminare: questo referente ha utenti invitati non disattivati. Disattiva o riassegna prima gli utenti collegati.');
        }

        // I consensi sono legati tramite owner polimorfico senza foreign key reale:
        // vanno ripuliti esplicitamente per non lasciare righe orfane.
        Consent::where('owner_type', 'wn_plus_account')
            ->where('owner_id', $account->id)
            ->delete();

        ConsentRequest::where('owner_type', 'wn_plus_account')
            ->where('owner_id', $account->id)
            ->delete();

        $account->delete();

        return redirect()
            ->route('wn-plus.accounts.index')
            ->with('success', 'Account WN+ eliminato correttamente.');
    }

    public function suspend(WnPlusAccount $account)
    {
        if ($account->status === 'suspended') {
            return back()->with('error', 'Questo account è già sospeso.');
        }

        if ($account->hasActiveInvitedAccounts()) {
            return back()->with('error', 'Impossibile sospendere: questo referente ha utenti invitati non disattivati. Disattiva o riassegna prima gli utenti collegati.');
        }

        $account->update(['status' => 'suspended']);

        return back()->with('success', 'Account sospeso correttamente.');
    }

    public function reactivate(WnPlusAccount $account)
    {
        if (! in_array($account->status, ['suspended', 'disabled'], true)) {
            return back()->with('error', 'Questo account non risulta sospeso o disabilitato.');
        }

        $account->update(['status' => 'active']);

        return back()->with('success', 'Account riattivato correttamente.');
    }

    public function disable(WnPlusAccount $account)
    {
        if ($account->status === 'disabled') {
            return back()->with('error', 'Questo account è già disabilitato.');
        }

        if ($account->hasActiveInvitedAccounts()) {
            return back()->with('error', 'Impossibile disabilitare: questo referente ha utenti invitati non disattivati. Disattiva o riassegna prima gli utenti collegati.');
        }

        $account->update(['status' => 'disabled']);

        return back()->with('success', 'Account disabilitato correttamente.');
    }

}
