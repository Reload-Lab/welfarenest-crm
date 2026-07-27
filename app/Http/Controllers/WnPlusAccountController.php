<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WnPlusAccount;
use App\Models\Organization;
use App\Models\Person;
use App\Models\PersonOrganizationRelation;
use App\Models\WnPlusLevel;
use App\Models\WnPlusRole;
use App\Models\WnPlusInvitation;
use App\Mail\WnPlusInvitationMail;
use App\Models\Consent;
use App\Models\ConsentRequest;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class WnPlusAccountController extends Controller
{
    public function index()
    {
        $accounts = WnPlusAccount::query()
            ->with([
                'organization',
                'role',
                'level',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25);

        return view('wn-plus.accounts.index', compact('accounts'));
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
            'max_users' => ['nullable', 'integer', 'min:0'],
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
            'max_users' => $validated['max_users'] ?? 8,
            'account_type' => 'manager',
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('wn-plus.accounts.index')
            ->with('success', 'Referente WN+ creato correttamente.');
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
            'max_users' => ['nullable', 'integer', 'min:0'],
        ]);

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

        if ($account->available_slots <= 0) {
            return redirect()
                ->route('wn-plus.accounts.show', $account)
                ->with('error', 'Il referente ha già raggiunto il numero massimo di utenti.');
        }

        return view('wn-plus.accounts.users.create', compact('account'));
    }

    public function storeUser(Request $request, WnPlusAccount $account)
    {
        $account->load(['level']);

        if ($account->account_type !== 'manager') {
            abort(404);
        }

        if ($account->available_slots <= 0) {
            return redirect()
                ->route('wn-plus.accounts.show', $account)
                ->with('error', 'Il referente ha già raggiunto il numero massimo di utenti.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:wn_plus_accounts,email'],
        ]);

        $userRole = WnPlusRole::where('code', 'user')->firstOrFail();

        WnPlusAccount::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $account->organization_id,
            'person_id' => null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'wn_plus_role_id' => $userRole->id,
            'wn_plus_level_id' => $account->wn_plus_level_id,
            'status' => 'invited',
            'account_type' => 'user',
            'max_users' => null,
            'invited_by_account_id' => $account->id,
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('wn-plus.accounts.show', $account)
            ->with('success', 'Utente WN+ creato correttamente.');
    }


    public function sendInvitation(WnPlusAccount $account)
    {
        if ($account->status === 'active') {
            return back()->with('error', 'Questo account è già attivo.');
        }

        $account->invitations()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->update([
                'expires_at' => now(),
            ]);

        $invitation = WnPlusInvitation::create([
            'wn_plus_account_id' => $account->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'sent_at' => now(),
        ]);

        Mail::to($account->email)->send(new WnPlusInvitationMail($invitation));

        return back()->with('success', 'Invito inviato correttamente a ' . $account->email . '.');
    }

    public function destroy(WnPlusAccount $account)
    {
        $hasActiveInvitedAccounts = $account->invitedAccounts()
            ->where('status', '!=', 'disabled')
            ->exists();

        if ($hasActiveInvitedAccounts) {
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

}
