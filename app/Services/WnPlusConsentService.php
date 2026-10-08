<?php

namespace App\Services;

use App\Models\Consent;
use App\Models\ConsentType;
use App\Models\ConsentVersion;
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
     * Stato corrente dei consensi per un insieme di account, in una sola query.
     *
     * Serve agli endpoint API: risolvere consenso per consenso con
     * ConsentService::latest() significherebbe una query per ogni tipo per ogni
     * account, cioe' otto per riga.
     *
     * L'ordinamento usa created_at E id: i consensi sono eventi e vengono scritti
     * in blocco nella stessa richiesta, quindi piu' righe possono condividere il
     * secondo. Senza l'id come discriminante, "l'ultimo" sarebbe arbitrario.
     *
     * @param  array<int, int>  $accountIds
     * @return array<int, array<string, array{status: string, version_code: ?string, at: ?string}>>
     */
    public function latestForAccounts(array $accountIds): array
    {
        if (empty($accountIds)) {
            return [];
        }

        $rows = Consent::query()
            ->with(['consentType:id,code', 'consentVersion:id,version_code'])
            ->where('owner_type', 'wn_plus_account')
            ->whereIn('owner_id', $accountIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $latest = [];

        foreach ($rows as $row) {
            $code = $row->consentType?->code;

            if ($code === null || isset($latest[$row->owner_id][$code])) {
                continue;
            }

            $latest[$row->owner_id][$code] = [
                'status' => $row->status,
                'version_code' => $row->consentVersion?->version_code,
                'at' => $row->granted_at?->toAtomString()
                    ?? $row->denied_at?->toAtomString()
                    ?? $row->created_at?->toAtomString(),
            ];
        }

        return $latest;
    }

    /**
     * Un consenso di visibilita' vale solo se e' concesso e, per email e telefono,
     * solo se lo e' anche il profilo base. Il vincolo e' gia' applicato in
     * scrittura: qui lo riapplichiamo in lettura, perche' dati raccolti prima di
     * questa regola o modificati a mano non devono poter pubblicare nulla.
     *
     * @param  array<string, array{status: string, version_code: ?string, at: ?string}>  $consents
     */
    public function isVisible(array $consents, string $code): bool
    {
        $granted = fn (string $c) => ($consents[$c]['status'] ?? null) === 'granted';

        if (! $granted(ConsentType::PROFILE_VISIBILITY_BASIC)) {
            return false;
        }

        return $granted($code);
    }

    /**
     * Percorso del PDF dell'informativa da mostrare a questo account.
     *
     * Non è un dettaglio di comodo: l'informativa dev'essere consultabile nel
     * momento in cui si danno i consensi, altrimenti la presa visione non è
     * informata. Referente e membro hanno documenti diversi, quindi la scelta
     * passa dallo stesso version_code usato per registrare i consensi.
     */
    public function privacyNoticePath(WnPlusAccount $account): ?string
    {
        return ConsentVersion::query()
            ->whereHas('consentType', fn ($query) => $query->where('code', ConsentType::PRIVACY_NOTICE))
            ->where('version_code', $this->versionCodeFor($account))
            ->where('is_active', true)
            ->value('content_file_path');
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
