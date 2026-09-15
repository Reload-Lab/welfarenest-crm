<?php

namespace App\Services\Import;

use App\Models\Address;
use App\Models\ContactPoint;
use App\Models\ImportBatch;
use App\Models\ImportRowResult;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

/**
 * Esegue l'importazione a partire dall'analisi.
 *
 * Scrive esclusivamente tramite i model, mai passando dai controller: il
 * ContactPointController genera automaticamente una richiesta di consenso per
 * i recapiti email delle persone, e in fase 2 un import massivo ne spedirebbe
 * un centinaio.
 */
class ImportExecutor
{
    private const OWNER_TYPE = 'organization';

    public function __construct(private readonly OrganizationImportAnalyzer $analyzer) {}

    public function analyze(string $path): array
    {
        return $this->analyzer->analyze($path);
    }

    /** Importazione già eseguita con lo stesso identico file, se c'è. */
    public function previousBatchFor(string $path): ?ImportBatch
    {
        return ImportBatch::query()
            ->where('file_hash', hash_file('sha256', $path))
            ->where('status', ImportBatch::STATUS_COMPLETED)
            ->latest('id')
            ->first();
    }

    /**
     * @param  list<int>  $excludedRows  righe del foglio Organizzazioni da non importare
     * @return array{batch: ImportBatch, analysis: array}
     */
    public function execute(string $path, ?int $userId = null, array $excludedRows = []): array
    {
        $analysis = $this->analyzer->analyze($path);
        $excluded = array_flip($excludedRows);

        $batch = DB::transaction(function () use ($path, $userId, $analysis, $excluded) {
            $batch = ImportBatch::create([
                'user_id' => $userId,
                'filename' => basename($path),
                'file_hash' => hash_file('sha256', $path),
                'status' => ImportBatch::STATUS_COMPLETED,
            ]);

            $counters = [
                'organizations_created' => 0,
                'addresses_created' => 0,
                'contacts_created' => 0,
                'blocks_skipped' => 0,
                'blocks_excluded' => 0,
            ];

            foreach ($analysis['blocks'] as $block) {
                if ($block['action'] === OrganizationImportAnalyzer::ACTION_SKIP) {
                    $counters['blocks_skipped']++;

                    continue;
                }

                if ($block['action'] === OrganizationImportAnalyzer::ACTION_ERROR || isset($excluded[$block['row']])) {
                    $counters['blocks_excluded']++;

                    continue;
                }

                $this->createBlock($batch, $block, $counters);
            }

            $batch->update($counters);

            return $batch;
        });

        return ['batch' => $batch->refresh(), 'analysis' => $analysis];
    }

    private function createBlock(ImportBatch $batch, array $block, array &$counters): void
    {
        $data = $block['data'];

        $organization = Organization::create([
            'name' => $data['name'],
            'legal_name' => $data['legal_name'],
            'organization_type_id' => $data['organization_type_id'],
            'vat_number' => $data['vat_number'],
            'tax_code' => $data['tax_code'],
            'sdi_code' => $data['sdi_code'],
            'is_split_payment' => $data['is_split_payment'],
            'is_active' => true,
        ]);

        $counters['organizations_created']++;

        $this->record($batch, OrganizationWorkbookReader::SHEET_ORGANIZATIONS, $block['row'],
            ImportRowResult::ENTITY_ORGANIZATION, $organization->id);

        foreach ($data['role_ids'] as $roleId) {
            $assignmentId = DB::table('organization_role_assignments')->insertGetId([
                'organization_id' => $organization->id,
                'organization_role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->record($batch, OrganizationWorkbookReader::SHEET_ORGANIZATIONS, $block['row'],
                ImportRowResult::ENTITY_ROLE_ASSIGNMENT, $assignmentId);
        }

        foreach ($block['addresses'] as $entry) {
            $address = Address::create([
                'owner_type' => self::OWNER_TYPE,
                'owner_id' => $organization->id,
                'address_type_id' => $entry['data']['address_type_id'],
                'label' => null,
                'street' => $entry['data']['street'],
                'street_number' => $entry['data']['street_number'],
                'postal_code' => $entry['data']['postal_code'],
                'city' => $entry['data']['city'],
                'province' => $entry['data']['province'],
                'region' => $entry['data']['region'],
                'country' => $entry['data']['country'],
                'is_primary' => $entry['data']['is_primary'],
            ]);

            $counters['addresses_created']++;

            $this->record($batch, OrganizationWorkbookReader::SHEET_ADDRESSES, $entry['row'],
                ImportRowResult::ENTITY_ADDRESS, $address->id);
        }

        foreach ($block['contacts'] as $entry) {
            $contactPoint = ContactPoint::create([
                'owner_type' => self::OWNER_TYPE,
                'owner_id' => $organization->id,
                'contact_type_id' => $entry['data']['contact_type_id'],
                'contact_usage_id' => $entry['data']['contact_usage_id'],
                'value' => $entry['data']['value'],
                'label' => null,
                'is_primary' => $entry['data']['is_primary'],
                'is_active' => true,
            ]);

            $counters['contacts_created']++;

            $this->record($batch, OrganizationWorkbookReader::SHEET_CONTACTS, $entry['row'],
                ImportRowResult::ENTITY_CONTACT_POINT, $contactPoint->id);
        }
    }

    private function record(ImportBatch $batch, string $sheet, int $row, string $entityType, int $entityId): void
    {
        ImportRowResult::create([
            'import_batch_id' => $batch->id,
            'sheet' => $sheet,
            'row_number' => $row,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}
