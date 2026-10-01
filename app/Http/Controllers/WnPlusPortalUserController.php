<?php

namespace App\Http\Controllers;

use App\Models\WnPlusAccount;
use App\Services\WnPlusInvitationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestione dei componenti della propria organizzazione, dall'area riservata.
 *
 * Riservata ai referenti: l'informativa del membro dice espressamente che "il
 * membro non puo' invitare altri componenti, salvo attribuzione espressa del
 * ruolo di referente". Il controllo e' su ogni azione e non solo sul menu.
 */
class WnPlusPortalUserController extends Controller
{
    public function index(Request $request)
    {
        $account = $this->manager($request);

        $users = $account->invitedAccounts()
            ->where('account_type', 'user')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('wn-plus.portal.users.index', compact('account', 'users'));
    }

    public function store(Request $request, WnPlusInvitationService $invitations)
    {
        $account = $this->manager($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('wn_plus_accounts', 'email')],
        ], [], [
            'first_name' => 'nome',
            'last_name' => 'cognome',
            'email' => 'email',
        ]);

        // created_by_user_id resta nullo: non e' un operatore del CRM ad aver creato
        // questo account, e invited_by_account_id dice gia' chi e' stato.
        $member = $invitations->createMember($account, $validated);

        $invitations->send($member, 'wn_plus_portal');

        return redirect()
            ->route('wn-plus.portal.users.index')
            ->with('success', 'Invito inviato a ' . $member->email . '.');
    }

    public function resend(Request $request, WnPlusAccount $user, WnPlusInvitationService $invitations)
    {
        $account = $this->manager($request);

        $this->assertOwned($account, $user);

        if ($user->status === 'active') {
            return back()->with('error', 'Questo utente ha già attivato il suo account.');
        }

        $invitations->send($user, 'wn_plus_portal');

        return back()->with('success', 'Invito inviato di nuovo a ' . $user->email . '.');
    }

    /**
     * Il referente dalla sessione. Un membro che arriva qui a mano riceve 403, non
     * una pagina vuota: e' un permesso che non ha, non un elenco senza risultati.
     */
    private function manager(Request $request): WnPlusAccount
    {
        $account = $request->attributes->get('wnPlusAccount');

        abort_unless($account->account_type === 'manager', 403);

        return $account;
    }

    /**
     * Un referente puo' agire solo sui membri che ha invitato lui. Senza questo
     * controllo l'id nell'URL basterebbe a toccare gli utenti di un'altra azienda.
     */
    private function assertOwned(WnPlusAccount $manager, WnPlusAccount $user): void
    {
        abort_unless(
            $user->invited_by_account_id === $manager->id && $user->account_type === 'user',
            403
        );
    }
}
