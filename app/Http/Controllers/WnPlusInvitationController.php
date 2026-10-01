<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\ConsentVersion;
use App\Models\WnPlusInvitation;
use App\Services\WnPlusConsentService;
use App\Support\AccessLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class WnPlusInvitationController extends Controller
{
    public function accept(string $token, WnPlusConsentService $wnPlusConsents)
    {
        $invitation = WnPlusInvitation::query()
            ->with('account.organization')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->expires_at->isPast()) {
            abort(410, 'Invito scaduto.');
        }

        // Il testo dei consensi dipende dall'informativa del ruolo (12 referente,
        // 13 membro): la vista lo risolve da config/consent_statements.php.
        $versionCode = $wnPlusConsents->versionCodeFor($invitation->account);

        $consentVersions = ConsentVersion::query()
            ->with('consentType')
            ->where('version_code', $versionCode)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (ConsentVersion $version) => $version->consentType->code);

        return view('wn-plus.invitations.accept', compact('invitation', 'versionCode', 'consentVersions'));
    }

    public function complete(Request $request, string $token, WnPlusConsentService $wnPlusConsents)
    {
        $invitation = WnPlusInvitation::query()
            ->with('account')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->expires_at->isPast()) {
            abort(410, 'Invito scaduto.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'privacy_base' => ['accepted'],
        ] + $wnPlusConsents->validationRules());


        $account = DB::transaction(function () use ($invitation, $validated, $wnPlusConsents) {
            $account = $invitation->account;

            $account->update([
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $wnPlusConsents->recordPrivacyNotice($account, 'wn_plus_onboarding');

            // Le sette scelte facoltative, comprese quelle non spuntate: l'account
            // nasce con il quadro consensi completo invece che a metà.
            $wnPlusConsents->recordSelfManaged($account, $validated, 'wn_plus_onboarding');

            return $account;
        });

        // Chi ha appena scelto la password è autenticato: farlo ripartire da un form
        // di login, subito dopo averla impostata, è un passaggio a vuoto. La sessione
        // è la stessa che usa il provider OIDC, quindi anche l'ingresso nel sito WN+
        // avviene senza chiedere di nuovo le credenziali.
        $request->session()->regenerate();

        $request->session()->put('wn_plus_account_id', $account->id);

        AccessLogger::record(AccessLog::EVENT_WN_PLUS_LOGIN, null, [
            'wn_plus_account_id' => $account->id,
            'email' => $account->email,
            'source' => 'wn_plus_onboarding',
        ]);

        return redirect()
            ->to($this->afterActivationUrl())
            ->with('success', 'Account attivato. Benvenuto in Welfare Nest Plus.');
    }

    /**
     * Dove atterra chi ha appena attivato l'account.
     *
     * Finché il sito WN+ non espone un indirizzo che avvia il login OIDC, la
     * destinazione sensata è l'area riservata sul CRM: mandarlo sulla home del sito
     * lo lascerebbe anonimo, perché WordPress non avvia la procedura da solo.
     * Quando quell'indirizzo esisterà basta valorizzare WN_PLUS_AFTER_ACTIVATION_URL
     * e l'ingresso diventa diretto, senza credenziali, grazie alla sessione appena
     * creata qui.
     */
    private function afterActivationUrl(): string
    {
        $configured = trim((string) config('services.wn_plus_site.after_activation_url'));

        return $configured !== ''
            ? $configured
            : route('wn-plus.portal.dashboard');
    }
}
