<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ConsentType;
use App\Models\ConsentVersion;
use App\Models\WnPlusAccount;
use App\Services\ConsentService;

class WnPlusPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');
        $account->load(['organization', 'role', 'level']);

        return view('wn-plus.portal.dashboard', compact('account'));
    }

    public function profile(Request $request)
    {
        $account = $request->attributes->get('wnPlusAccount');
        $account->load(['organization', 'role', 'level', 'consents.consentType']);

        // Il version_code decide sia la versione a cui aggancereremo i consensi salvati,
        // sia la variante di testo da mostrare: referente e membro hanno informative
        // diverse e, per email e telefono, formulazioni diverse.
        $versionCode = $this->versionCodeFor($account);

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

    public function updateConsents(Request $request, ConsentService $consentService)
    {
        $account = $request->attributes->get('wnPlusAccount');

        $rules = [];

        foreach (ConsentType::WN_PLUS_SELF_MANAGED as $code) {
            $rules[$code] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($rules);

        $choices = [];

        foreach (ConsentType::WN_PLUS_SELF_MANAGED as $code) {
            $choices[$code] = (bool) ($validated[$code] ?? false);
        }

        // Regola dell'informativa: la visibilità di email e telefono è attivabile soltanto
        // se è visibile il profilo base. La applichiamo qui e non solo nel form, perché è
        // una condizione di liceità della pubblicazione, non un vincolo di interfaccia.
        if (! $choices[ConsentType::PROFILE_VISIBILITY_BASIC]) {
            $choices[ConsentType::PROFILE_VISIBILITY_EMAIL] = false;
            $choices[ConsentType::PROFILE_VISIBILITY_PHONE] = false;
        }

        $versionCode = $this->versionCodeFor($account);

        foreach ($choices as $code => $granted) {
            $granted
                ? $consentService->grant('wn_plus_account', $account->id, $code, 'wn_plus_portal', $versionCode)
                : $consentService->deny('wn_plus_account', $account->id, $code, 'wn_plus_portal', $versionCode);
        }

        return back()->with('success', 'Preferenze di consenso aggiornate correttamente.');
    }

    /**
     * Referente (manager) e membro (user) hanno informative distinte, quindi versioni di
     * consenso distinte. Senza questo parametro ConsentService sceglierebbe una versione
     * arbitraria tra le tante attive per lo stesso tipo.
     */
    private function versionCodeFor(WnPlusAccount $account): string
    {
        return $account->account_type === 'manager'
            ? '12_referente_wnplus_2026_v1'
            : '13_membro_wnplus_2026_v1';
    }
}