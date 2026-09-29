<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Il 10 settembre 2026 "aggiornamenti facoltativi sul servizio" di referente e membro WN+
 * era stato mappato su promotional_emails, in mancanza di un tipo dedicato. Con il testo
 * integrale delle informative 12 e 13 risulta che si tratta di due consensi distinti:
 * la newsletter promozionale sta nell'informativa 02 e si raccoglie nel flusso di
 * iscrizione, mentre gli aggiornamenti sull'area riservata sono una scelta a sé.
 *
 * Questa migration sposta i consensi già registrati sul nuovo tipo service_updates,
 * mantenendo stato, date e audit. I tipi e le versioni sono creati qui se mancano, così
 * la migration funziona anche quando gira prima del seeder (ordine tipico in deploy).
 */
return new class extends Migration
{
    private const VERSIONS = [
        'manager' => '12_referente_wnplus_2026_v1',
        'user' => '13_membro_wnplus_2026_v1',
    ];

    private const FILES = [
        '12_referente_wnplus_2026_v1' => 'consents/12_Informativa_Referente_Welfare_Nest_Plus.pdf',
        '13_membro_wnplus_2026_v1' => 'consents/13_Informativa_Membro_Welfare_Nest_Plus.pdf',
    ];

    public function up(): void
    {
        $promotionalId = $this->consentTypeId('promotional_emails');

        if (! $promotionalId) {
            return;
        }

        $serviceUpdatesId = $this->ensureServiceUpdatesType();
        $serviceVersions = $this->ensureServiceUpdatesVersions($serviceUpdatesId);

        // I consensi da spostare sono quelli degli account WN+ raccolti nei flussi WN+:
        // l'onboarding dell'invito e il form del portale. Un eventuale consenso newsletter
        // di un account WN+ raccolto altrove resta dov'è.
        $rows = DB::table('consents')
            ->where('owner_type', 'wn_plus_account')
            ->where('consent_type_id', $promotionalId)
            ->whereIn('source', ['wn_plus_onboarding', 'wn_plus_portal'])
            ->get(['id', 'owner_id']);

        if ($rows->isEmpty()) {
            return;
        }

        // Il version_code dipende dal ruolo dell'account (referente o membro).
        $accountTypes = DB::table('wn_plus_accounts')
            ->whereIn('id', $rows->pluck('owner_id')->unique())
            ->pluck('account_type', 'id');

        foreach ($rows as $row) {
            $versionCode = self::VERSIONS[$accountTypes[$row->owner_id] ?? 'user'] ?? self::VERSIONS['user'];

            DB::table('consents')
                ->where('id', $row->id)
                ->update([
                    'consent_type_id' => $serviceUpdatesId,
                    'consent_version_id' => $serviceVersions[$versionCode],
                    'updated_at' => now(),
                ]);
        }

        // Le due varianti di ripiego su promotional_emails non devono più essere proposte.
        DB::table('consent_versions')
            ->where('consent_type_id', $promotionalId)
            ->whereIn('version_code', array_values(self::VERSIONS))
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        $promotionalId = $this->consentTypeId('promotional_emails');
        $serviceUpdatesId = $this->consentTypeId('service_updates');

        if (! $promotionalId || ! $serviceUpdatesId) {
            return;
        }

        DB::table('consent_versions')
            ->where('consent_type_id', $promotionalId)
            ->whereIn('version_code', array_values(self::VERSIONS))
            ->update(['is_active' => true, 'updated_at' => now()]);

        $promotionalVersions = DB::table('consent_versions')
            ->where('consent_type_id', $promotionalId)
            ->whereIn('version_code', array_values(self::VERSIONS))
            ->pluck('id', 'version_code');

        $serviceVersions = DB::table('consent_versions')
            ->where('consent_type_id', $serviceUpdatesId)
            ->whereIn('version_code', array_values(self::VERSIONS))
            ->pluck('version_code', 'id');

        DB::table('consents')
            ->where('owner_type', 'wn_plus_account')
            ->where('consent_type_id', $serviceUpdatesId)
            ->whereIn('source', ['wn_plus_onboarding', 'wn_plus_portal'])
            ->get(['id', 'consent_version_id'])
            ->each(function ($row) use ($promotionalId, $promotionalVersions, $serviceVersions) {
                $versionCode = $serviceVersions[$row->consent_version_id] ?? null;

                DB::table('consents')
                    ->where('id', $row->id)
                    ->update([
                        'consent_type_id' => $promotionalId,
                        'consent_version_id' => $versionCode ? ($promotionalVersions[$versionCode] ?? null) : null,
                        'updated_at' => now(),
                    ]);
            });
    }

    private function consentTypeId(string $code): ?int
    {
        return DB::table('consent_types')->where('code', $code)->value('id');
    }

    private function ensureServiceUpdatesType(): int
    {
        $id = $this->consentTypeId('service_updates');

        if ($id) {
            return $id;
        }

        return DB::table('consent_types')->insertGetId([
            'code' => 'service_updates',
            'name' => 'Aggiornamenti facoltativi sul servizio',
            'category' => 'consent',
            'description' => 'Avvisi non strettamente necessari su nuove funzionalità e contenuti dell\'area riservata',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @return array<string, int> version_code => consent_version id
     */
    private function ensureServiceUpdatesVersions(int $consentTypeId): array
    {
        $existing = DB::table('consent_versions')
            ->where('consent_type_id', $consentTypeId)
            ->whereIn('version_code', array_values(self::VERSIONS))
            ->pluck('id', 'version_code')
            ->all();

        foreach (self::VERSIONS as $accountType => $versionCode) {
            if (isset($existing[$versionCode])) {
                continue;
            }

            $existing[$versionCode] = DB::table('consent_versions')->insertGetId([
                'consent_type_id' => $consentTypeId,
                'version_code' => $versionCode,
                'title' => 'Aggiornamenti facoltativi sul servizio — ' .
                    ($accountType === 'manager' ? 'referente Welfare Nest Plus' : 'membro Welfare Nest Plus'),
                'content_file_path' => self::FILES[$versionCode],
                'published_at' => now(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $existing;
    }
};
