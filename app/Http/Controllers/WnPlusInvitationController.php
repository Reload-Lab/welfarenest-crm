<?php

namespace App\Http\Controllers;

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

        return view('wn-plus.invitations.accept', compact('invitation'));
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
            'promotional_emails' => ['nullable', 'boolean'],
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

            // Versione dell'informativa/consenso specifica per ruolo: il referente (manager) e il
            // membro (user) hanno testi diversi (12_referente_wnplus / 13_membro_wnplus). Senza questo
            // parametro ConsentService sceglierebbe una versione arbitraria tra le tante ora attive.
            $versionCode = $account->account_type === 'manager'
                ? '12_referente_wnplus_2026_v1'
                : '13_membro_wnplus_2026_v1';

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

            // NB: per promotional_emails non esiste ancora una versione specifica per referente/membro WN+
            // (solo "02_newsletter_2026_v1" e "06_preferenza_aziendale_2026_v1", nessuna delle due corretta
            // per questo contesto) — lasciato senza versionCode esplicito finché non si decide come trattarlo,
            // vedi nota in claude/analisi-inviti-wnplus.md.
            ($validated['promotional_emails'] ?? false)
                ? $consentService->grant('wn_plus_account', $account->id, 'promotional_emails', 'wn_plus_onboarding')
                : $consentService->deny('wn_plus_account', $account->id, 'promotional_emails', 'wn_plus_onboarding');
        });

        return redirect()
            ->away('https://plus.welfarenest.it/')
            ->with('success', 'Account WN+ attivato correttamente. Ora puoi accedere.');
    }




}