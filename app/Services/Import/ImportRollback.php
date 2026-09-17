<?php

namespace App\Services\Import;

use App\Models\ImportBatch;
use App\Models\ImportRowResult;
use Illuminate\Support\Facades\DB;

/**
 * Annulla un'importazione usando il tracciamento in import_row_results.
 *
 * Gli indirizzi, i recapiti, le note e i consensi sono collegati tramite
 * owner_type/owner_id, senza foreign key: cancellare l'organizzazione non li
 * porta via. Vanno quindi gestiti esplicitamente, e va rilevato il caso in cui
 * qualcuno ne abbia aggiunti dal CRM dopo l'importazione.
 */
class ImportRollback
{
    private const OWNER_TYPE = 'organization';

    /** Tabella polimorfica => entity_type tracciato, oppure null se l'import non ne crea mai. */
    private const POLYMORPHIC_TABLES = [
        'addresses' => ImportRowResult::ENTITY_ADDRESS,
        'contact_points' => ImportRowResult::ENTITY_CONTACT_POINT,
        'notes' => null,
        'consents' => null,
        'consent_requests' => null,
    ];

    /** @return list<int> */
    public function organizationIds(ImportBatch $batch): array
    {
        return $batch->rowResults()
            ->where('entity_type', ImportRowResult::ENTITY_ORGANIZATION)
            ->pluck('entity_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Righe collegate alle organizzazioni del batch che il batch NON ha creato:
     * aggiunte a mano dal CRM dopo l'importazione.
     *
     * @return array<string, int>
     */
    public function foreignData(ImportBatch $batch): array
    {
        $organizationIds = $this->organizationIds($batch);

        if ($organizationIds === []) {
            return [];
        }

        $foreign = [];

        foreach (self::POLYMORPHIC_TABLES as $table => $entityType) {
            $query = DB::table($table)
                ->where('owner_type', self::OWNER_TYPE)
                ->whereIn('owner_id', $organizationIds);

            if ($entityType !== null) {
                $query->whereNotIn('id', $this->entityIds($batch, $entityType));
            }

            $count = $query->count();

            if ($count > 0) {
                $foreign[$table] = $count;
            }
        }

        return $foreign;
    }

    /**
     * @return array<string, int> conteggio delle righe eliminate per tabella
     */
    public function rollback(ImportBatch $batch, bool $deleteForeignData = false): array
    {
        return DB::transaction(function () use ($batch, $deleteForeignData) {
            $organizationIds = $this->organizationIds($batch);
            $deleted = [];

            if ($organizationIds !== []) {
                foreach (self::POLYMORPHIC_TABLES as $table => $entityType) {
                    $query = DB::table($table)
                        ->where('owner_type', self::OWNER_TYPE)
                        ->whereIn('owner_id', $organizationIds);

                    if ($entityType !== null && ! $deleteForeignData) {
                        $query->whereIn('id', $this->entityIds($batch, $entityType));
                    }

                    $count = $query->delete();

                    if ($count > 0) {
                        $deleted[$table] = $count;
                    }
                }

                // Le assegnazioni di ruolo, le relazioni con le persone e gli
                // account WN Plus hanno foreign key in cascata: spariscono da soli.
                $deleted['organizations'] = DB::table('organizations')
                    ->whereIn('id', $organizationIds)
                    ->delete();
            }

            // import_row_results scende in cascata con il batch.
            $batch->delete();

            return $deleted;
        });
    }

    /** @return list<int> */
    private function entityIds(ImportBatch $batch, string $entityType): array
    {
        return $batch->rowResults()
            ->where('entity_type', $entityType)
            ->pluck('entity_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
