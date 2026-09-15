<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\DB;

/**
 * Fotografia delle organizzazioni già presenti in archivio, indicizzata per
 * identificativi fiscali e per denominazione normalizzata.
 *
 * Il confronto sui nomi è incrociato: name e legal_name del file vengono
 * confrontati con name e legal_name di ogni record esistente.
 */
class ExistingOrganizationIndex
{
    private array $records = [];
    private array $byVatNumber = [];
    private array $byTaxCode = [];
    private array $byNameKey = [];

    public static function build(): self
    {
        $index = new self();

        $rows = DB::table('organizations')
            ->get(['id', 'name', 'legal_name', 'vat_number', 'tax_code', 'sdi_code', 'organization_type_id', 'is_split_payment']);

        foreach ($rows as $row) {
            $record = (array) $row;
            $index->records[$row->id] = $record;

            if (! empty($row->vat_number)) {
                $index->byVatNumber[$row->vat_number] ??= $row->id;
            }

            if (! empty($row->tax_code)) {
                $index->byTaxCode[$row->tax_code] ??= $row->id;
            }

            foreach (OrganizationNameKey::allOf($row->name, $row->legal_name) as $key) {
                $index->byNameKey[$key] ??= $row->id;
            }
        }

        return $index;
    }

    public function count(): int
    {
        return count($this->records);
    }

    /**
     * Cerca una corrispondenza per la riga del file.
     *
     * @return array{id: int, display: string, matched_on: string}|null
     */
    public function match(array $data): ?array
    {
        if (! empty($data['vat_number']) && isset($this->byVatNumber[$data['vat_number']])) {
            return $this->describe($this->byVatNumber[$data['vat_number']], 'partita IVA');
        }

        if (! empty($data['tax_code']) && isset($this->byTaxCode[$data['tax_code']])) {
            return $this->describe($this->byTaxCode[$data['tax_code']], 'codice fiscale');
        }

        foreach (OrganizationNameKey::allOf($data['name'] ?? null, $data['legal_name'] ?? null) as $key) {
            if (isset($this->byNameKey[$key])) {
                return $this->describe($this->byNameKey[$key], 'denominazione');
            }
        }

        return null;
    }

    /**
     * Campi in cui il file differisce dal record esistente.
     * Un campo vuoto nel file non è una differenza: il file può essere meno completo.
     *
     * @return list<array{field: string, file: mixed, crm: mixed}>
     */
    public function differences(int $id, array $data): array
    {
        $record = $this->records[$id] ?? null;

        if ($record === null) {
            return [];
        }

        $differences = [];

        foreach (['name', 'legal_name', 'vat_number', 'tax_code', 'sdi_code', 'organization_type_id'] as $field) {
            $fileValue = $data[$field] ?? null;

            if ($fileValue === null || $fileValue === '') {
                continue;
            }

            $crmValue = $record[$field] ?? null;

            if ((string) $fileValue !== (string) $crmValue) {
                $differences[] = ['field' => $field, 'file' => $fileValue, 'crm' => $crmValue];
            }
        }

        return $differences;
    }

    private function describe(int $id, string $matchedOn): array
    {
        $record = $this->records[$id];

        return [
            'id' => $id,
            'display' => $record['name'] ?: $record['legal_name'] ?: "organizzazione #{$id}",
            'matched_on' => $matchedOn,
        ];
    }
}
