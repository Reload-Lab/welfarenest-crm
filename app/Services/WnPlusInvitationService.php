<?php

namespace App\Services;

use App\Mail\WnPlusInvitationMail;
use App\Models\WnPlusAccount;
use App\Models\WnPlusInvitation;
use App\Models\WnPlusRole;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Creazione di un membro WN+ e invio dell'invito.
 *
 * Esiste perche' gli stessi due gesti avvengono da due parti: l'operatore dal
 * back office CRM e, dal 30/09/2026, il referente dalla propria area riservata.
 * Le regole — ruolo 'user', livello ereditato dal referente, organizzazione
 * ereditata, scadenza a 7 giorni, invalidazione degli inviti precedenti — devono
 * valere identiche, e duplicarle nei due controller significava prima o poi
 * correggerne una sola.
 */
class WnPlusInvitationService
{
    private const EXPIRES_AFTER_DAYS = 7;

    /**
     * Crea un membro sotto un referente. Organizzazione e livello si ereditano dal
     * referente: e' cio' che impedisce a un referente di creare account fuori dalla
     * propria organizzazione, anche manomettendo il form.
     *
     * @param  array{first_name: string, last_name: string, email: string}  $data
     */
    public function createMember(
        WnPlusAccount $manager,
        array $data,
        ?int $createdByUserId = null
    ): WnPlusAccount {
        $userRole = WnPlusRole::where('code', 'user')->firstOrFail();

        return WnPlusAccount::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $manager->organization_id,
            'person_id' => null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'wn_plus_role_id' => $userRole->id,
            'wn_plus_level_id' => $manager->wn_plus_level_id,
            'status' => 'invited',
            'account_type' => 'user',
            'invited_by_account_id' => $manager->id,
            'created_by_user_id' => $createdByUserId,
        ]);
    }

    /**
     * Genera e spedisce un invito, invalidando quelli ancora aperti: due link validi
     * contemporaneamente per lo stesso account sono un invito a confondersi.
     *
     * @param  string  $source  chi lo ha originato: 'crm' oppure 'wn_plus_portal'
     */
    public function send(WnPlusAccount $account, string $source = 'crm'): WnPlusInvitation
    {
        $account->invitations()
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        $invitation = WnPlusInvitation::create([
            'wn_plus_account_id' => $account->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(self::EXPIRES_AFTER_DAYS),
            'sent_at' => now(),
        ]);

        Mail::to($account->email)->send(new WnPlusInvitationMail($invitation));

        // Il referente che invita sta comunicando a Welfare Nest i dati di un'altra
        // persona: l'informativa lo prevede (art. 14) e l'operazione va tracciata.
        ActivityLogger::log(ActivityLogger::WN_PLUS_INVITATION_SENT, $account, [
            'email' => $account->email,
            'source' => $source,
            'invited_by_account_id' => $account->invited_by_account_id,
        ]);

        return $invitation;
    }
}
