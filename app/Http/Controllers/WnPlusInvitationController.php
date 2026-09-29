<?php

namespace App\Http\Controllers;

use App\Models\WnPlusAccount;
use App\Models\WnPlusInvitation;
use App\Services\ConsentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class WnPlusInvitationController extends Controller
{
    public function accept(string $token)
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
        $versionCode = $this->versionCodeFor($invitation->account);

        return view('wn-plus.invitations.accept', compact('invitation', 'versionCode'));
    }

    public function complete(Request $request, string $token, ConsentService $consentService)
    
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
            'image_disclosure' => ['nullable', 'boolean'],
            'service_updates' => ['nullable', 'boolean'],
        ]);

        
        DB::transaction(function () use ($invitation, $validated, $consentService) {
            $account = $invitation->account;

            $account->update([
                'password' => Hash::make($validated['password']),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $versionCode = $this->versionCodeFor($account);

            $consentService->grant(
                'wn_plus_account',
                $account->id,
                'privacy_notice',
                'wn_plus_onboarding',
                $versionCode
            );

            ($validated['image_disclosure'] ?? false)
                ? $consentService->grant('wn_plus_account', $account->id, 'image_disclosure', 'wn_plus_onboarding', $versionCode)
                : $consentService->deny('wn_plus_account', $account->id, 'image_disclosure', 'wn_plus_onboarding', $versionCode);

            // "Aggiornamenti facoltativi sul servizio": dal 29/9/2026 ha il suo tipo
            // service_updates (prima era mappato su promotional_emails). La newsletter
            // promozionale non si raccoglie qui: per WN+ passa dal flusso di iscrizione
            // dedicato, con la sua informativa (02).
            ($validated['service_updates'] ?? false)
                ? $consentService->grant('wn_plus_account', $account->id, 'service_updates', 'wn_plus_onboarding', $versionCode)
                : $consentService->deny('wn_plus_account', $account->id, 'service_updates', 'wn_plus_onboarding', $versionCode);

            // Le scelte di visibilità e le survey non compaiono nell'attivazione: si
            // gestiscono nell'area riservata, come previsto dalla matrice consensi.
        });

        return redirect()
            ->away('https://plus.welfarenest.it/')
            ->with('success', 'Account WN+ attivato correttamente. Ora puoi accedere.');
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