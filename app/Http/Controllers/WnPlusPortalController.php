<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\ConsentVersion;
use App\Services\WnPlusConsentService;

class WnPlusPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');
        $account->load(['organization', 'role', 'level']);

        return view('wn-plus.portal.dashboard', compact('account'));
    }

    public function profile(Request $request, WnPlusConsentService $wnPlusConsents)
    {
        $account = $request->attributes->get('wnPlusAccount');
        $account->load(['organization', 'role', 'level', 'consents.consentType']);

        // Il version_code decide sia la versione a cui aggancereremo i consensi salvati,
        // sia la variante di testo da mostrare: referente e membro hanno informative
        // diverse e, per email e telefono, formulazioni diverse.
        $versionCode = $wnPlusConsents->versionCodeFor($account);

        // Le versioni servono alla vista solo come ultima spiaggia per l'etichetta,
        // quando un testo non è ancora in config/consent_statements.php.
        $consentVersions = ConsentVersion::query()
            ->with('consentType')
            ->where('version_code', $versionCode)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (ConsentVersion $version) => $version->consentType->code);

        return view('wn-plus.portal.profile', compact('account', 'consentVersions', 'versionCode'));
    }

    /**
     * L'informativa del proprio ruolo, scaricabile dall'area riservata: i consensi
     * si possono cambiare in qualsiasi momento, e per farlo con cognizione bisogna
     * poter rileggere il documento.
     */
    public function informativa(Request $request, WnPlusConsentService $wnPlusConsents)
    {
        $account = $request->attributes->get('wnPlusAccount');

        $path = $wnPlusConsents->privacyNoticePath($account);

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    public function updatePassword(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $account->password)) {
            return back()->withErrors(['current_password' => 'Password attuale non corretta.']);
        }

        $account->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password aggiornata correttamente.');
    }

    public function updateConsents(Request $request, WnPlusConsentService $wnPlusConsents)
    {
        $account = $request->attributes->get('wnPlusAccount');

        $validated = $request->validate($wnPlusConsents->validationRules());

        $wnPlusConsents->recordSelfManaged($account, $validated, 'wn_plus_portal');

        return back()->with('success', 'Preferenze di consenso aggiornate correttamente.');
    }
}
