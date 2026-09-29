<?php

namespace App\Services;

use App\Models\ConsentType;
use App\Models\WnPlusAccount;

/**
 * Raccolta dei consensi che referente e membro WN+ esprimono su di sé.
 *
 * Esiste perché gli stessi sette consensi si raccolgono in due punti — la pagina
 * di attivazione dell'invito e "Il mio profilo" nel portale — e le regole che li
 * governano (quale versione dell'informativa, quale dipendenza fra le visibilità)
 * devono valere identiche in entrambi. Duplicarle nei due controller significava
 * prima o poi correggerne una sola.
 *
 * Resta nell'area WN+ e non dentro ConsentService, che è generico e non conosce
 * WnPlusAccount.
 */
class WnPlusConsentService
{
    public function __construct(private ConsentService $consents) {}

    /**
     * Regole di validazione per i sette consensi facoltativi.
     *
     * @return array<string, array<int, string>>
     */
    public function validationRules(): array
    {
        $rules = [];

        foreach (ConsentType::WN_PLUS_SELF_MANAGED as $code) {
            $rules[$code] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    /**
     * Normalizza le caselle ricevute e applica la regola dell'informativa: la
     * visibilità di email e telefono è attivabile soltanto se è visibile il
     * profilo base. Sta qui e non solo nel form perché è una condizione di
     * liceità della pubblicazione, non un vincolo di interfaccia: una POST
     * costruita a mano non deve poterla aggirare.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, bool>
     */
    public function normalizeChoices(array $input): array
    {
        $choices = [];

        foreach (ConsentType::WN_PLUS_SELF_MANAGED as $code) {
            $choices[$code] = (bool) ($input[$code] ?? false);
        }

        if (! $choices[ConsentType::PROFILE_VISIBILITY_BASIC]) {
            $choices[ConsentType::PROFILE_VISIBILITY_EMAIL] = false;
            $choices[ConsentType::PROFILE_VISIBILITY_PHONE] = false;
        }

        return $choices;
    }

    /**
     * Registra le sette scelte facoltative. Una casella non spuntata diventa un
     * 'denied' esplicito e non un'assenza di record: chi gestisce il CRM deve
     * poter distinguere "ha rifiutato" da "non ha ancora risposto".
     *
     * @param  array<string, mixed>  $input  i dati validati del form
     */
    public function recordSelfManaged(WnPlusAccount $account, array $input, string $source): void
    {
        $versionCode = $this->versionCodeFor($account);

        foreach ($this->normalizeChoices($input) as $code => $granted) {
            $granted
                ? $this->consents->grant('wn_plus_account', $account->id, $code, $source, $versionCode)
                : $this->consents->deny('wn_plus_account', $account->id, $code, $source, $versionCode);
        }
    }

    /**
     * Presa visione dell'informativa: obbligatoria, si registra una tantum
     * all'attivazione dell'account e non si tocca più dal portale.
     */
    public function recordPrivacyNotice(WnPlusAccount $account, string $source): void
    {
        $this->consents->grant(
            'wn_plus_account',
            $account->id,
            ConsentType::PRIVACY_NOTICE,
            $source,
            $this->versionCodeFor($account)
        );
    }

    /**
     * Referente (manager) e membro (user) hanno informative distinte, quindi
     * versioni di consenso distinte. Senza questo parametro ConsentService
     * sceglierebbe una versione arbitraria fra le tante attive per lo stesso tipo.
     */
    public function versionCodeFor(WnPlusAccount $account): string
    {
        return $account->account_type === 'manager'
            ? '12_referente_wnplus_2026_v1'
            : '13_membro_wnplus_2026_v1';
    }
}
