<?php

namespace App\Http\Controllers;

use App\Models\ConsentVersion;
use App\Models\WnPlusInvitation;
use App\Services\WnPlusConsentService;
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

        // Tutte le scelte dell'informativa si raccolgono qui, nel momento in cui la
        // persona la sta leggendo. Restano modificabili dall'area riservata.
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


        DB::transaction(function () use ($invitation, $validated, $wnPlusConsents) {
            $account = $invitation->account;

            $account->update([
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $wnPlusConsents->recordPrivacyNotice($account, 'wn_plus_onboarding');

            // Le sette scelte facoltative, comprese quelle non spuntate: l'account
            // nasce con il quadro consensi completo invece che a metà.
            $wnPlusConsents->recordSelfManaged($account, $validated, 'wn_plus_onboarding');
        });

        return redirect()
            ->away('https://plus.welfarenest.it/')
            ->with('success', 'Account WN+ attivato correttamente. Ora puoi accedere.');
    }
}
