<?php

namespace App\Services\Import;

/**
 * Analisi completa del file: valida ogni riga, ricostruisce i collegamenti fra
 * i tre fogli, applica i controlli che richiedono di vedere tutto il file
 * insieme, e confronta con le organizzazioni già in archivio.
 *
 * Non scrive nulla. L'esito proposto per ogni blocco è create, skip o error.
 */
class OrganizationImportAnalyzer
{
    public const ACTION_CREATE = 'create';
    public const ACTION_SKIP = 'skip';
    public const ACTION_ERROR = 'error';

    public function __construct(
        private readonly OrganizationWorkbookReader $reader,
        private readonly ImportRowValidator $validator,
        private readonly LookupResolver $lookups,
    ) {}

    public function analyze(string $path): array
    {
        $file = $this->reader->read($path);

        // Da qui in poi le anagrafiche si risolvono passando dai code dichiarati
        // nei fogli Fonte_ del file, non dalle etichette correnti.
        $this->lookups->useFileVocabulary($file['vocabulary']);

        [$blocks, $byNameKey] = $this->buildBlocks($file['sheets'][OrganizationWorkbookReader::SHEET_ORGANIZATIONS]);
        [$blocks, $orphans] = $this->attachChildren($file['sheets'], $blocks, $byNameKey);

        $existing = ExistingOrganizationIndex::build();

        foreach ($blocks as $i => $block) {
            $this->checkPrimaryFlags($blocks[$i]);
            $this->checkDuplicateContactValues($blocks[$i]);

            $match = $existing->match($blocks[$i]['data']);

            if ($match !== null) {
                $blocks[$i]['existing'] = $match;
                $blocks[$i]['differences'] = $existing->differences($match['id'], $blocks[$i]['data']);
            }

            $blocks[$i]['action'] = $this->decideAction($blocks[$i]);
        }

        return [
            'warnings' => $file['warnings'],
            'blocks' => $blocks,
            'orphans' => $orphans,
            'existing_count' => $existing->count(),
            'summary' => $this->summarize($blocks, $orphans),
        ];
    }

    /** @return array{0: list<array>, 1: array<string, int>} */
    private function buildBlocks(array $rows): array
    {
        $blocks = [];
        $byCode = [];
        $byNameKey = [];

        foreach ($rows as $row) {
            $result = $this->validator->validateOrganization($row);
            $data = $result['data'];

            $index = count($blocks);

            $blocks[$index] = [
                'row' => $row['_row'],
                'code' => $data['import_code'],
                'label' => $data['name'] ?: $data['legal_name'] ?: '(senza nome)',
                'data' => $data,
                'issues' => $result['issues'],
                'addresses' => [],
                'contacts' => [],
                'existing' => null,
                'differences' => [],
                'action' => null,
            ];

            if ($data['import_code'] !== null) {
                if (isset($byCode[$data['import_code']])) {
                    $other = $blocks[$byCode[$data['import_code']]]['row'];
                    $blocks[$index]['issues'][] = self::error(
                        'Codice_Organizzazione',
                        "il codice '{$data['import_code']}' è già usato alla riga {$other}"
                    );
                } else {
                    $byCode[$data['import_code']] = $index;
                }
            }

            foreach (OrganizationNameKey::allOf($data['name'], $data['legal_name']) as $key) {
                if (isset($byNameKey[$key]) && $byNameKey[$key] !== $index) {
                    $other = $blocks[$byNameKey[$key]]['row'];
                    $blocks[$index]['issues'][] = self::error(
                        'name',
                        "denominazione già presente alla riga {$other} dello stesso file"
                    );

                    continue;
                }

                $byNameKey[$key] = $index;
            }
        }

        return [$blocks, $byNameKey];
    }

    /** @return array{0: list<array>, 1: array<string, list<array>>} */
    private function attachChildren(array $sheets, array $blocks, array $byNameKey): array
    {
        $orphans = ['addresses' => [], 'contacts' => []];

        $config = [
            [OrganizationWorkbookReader::SHEET_ADDRESSES, 'validateAddress', 'addresses'],
            [OrganizationWorkbookReader::SHEET_CONTACTS, 'validateContact', 'contacts'],
        ];

        foreach ($config as [$sheet, $method, $bucket]) {
            foreach ($sheets[$sheet] as $row) {
                $result = $this->validator->{$method}($row);

                $entry = [
                    'row' => $row['_row'],
                    'data' => $result['data'],
                    'issues' => $result['issues'],
                ];

                $reference = $result['data']['organization_ref'];
                $key = OrganizationNameKey::of($reference);
                $index = $key === null ? null : ($byNameKey[$key] ?? null);

                if ($index === null) {
                    if ($reference !== null) {
                        $entry['issues'][] = self::error(
                            'Organizzazione',
                            "nessuna organizzazione chiamata '{$reference}' nel foglio Organizzazioni"
                        );
                    }

                    $orphans[$bucket][] = $entry;

                    continue;
                }

                $code = $result['data']['organization_code'];

                if ($code !== null && $blocks[$index]['code'] !== null && $code !== $blocks[$index]['code']) {
                    $entry['issues'][] = self::warning(
                        'Codice_Organizzazione',
                        "il codice '{$code}' non corrisponde a '{$blocks[$index]['code']}' dell'organizzazione collegata"
                    );
                }

                $blocks[$index][$bucket][] = $entry;
            }
        }

        return [$blocks, $orphans];
    }

    /** Un solo recapito e un solo indirizzo primario per organizzazione e tipo. */
    private function checkPrimaryFlags(array &$block): void
    {
        foreach (['addresses' => 'address_type_id', 'contacts' => 'contact_type_id'] as $bucket => $typeField) {
            $seen = [];

            foreach ($block[$bucket] as $i => $entry) {
                if (empty($entry['data']['is_primary'])) {
                    continue;
                }

                $type = $entry['data'][$typeField];

                if ($type === null) {
                    continue;
                }

                if (isset($seen[$type])) {
                    $block[$bucket][$i]['issues'][] = self::error(
                        'is_primary',
                        "già indicato come principale alla riga {$seen[$type]} per lo stesso tipo"
                    );

                    continue;
                }

                $seen[$type] = $entry['row'];
            }
        }
    }

    /** Lo stesso recapito ripetuto sulla stessa organizzazione. */
    private function checkDuplicateContactValues(array &$block): void
    {
        $seen = [];

        foreach ($block['contacts'] as $i => $entry) {
            $value = $entry['data']['value'];

            if ($value === null) {
                continue;
            }

            $key = mb_strtolower($value);

            if (isset($seen[$key])) {
                $block['contacts'][$i]['issues'][] = self::error(
                    'value',
                    "recapito già presente alla riga {$seen[$key]} per la stessa organizzazione"
                );

                continue;
            }

            $seen[$key] = $entry['row'];
        }
    }

    /**
     * Il blocco è un'unità: se un figlio è in errore, l'esito proposto per
     * tutto il blocco è l'esclusione, così l'operatore corregge il file
     * invece di creare un record monco che da Excel non si completa più.
     */
    private function decideAction(array $block): string
    {
        if (self::hasError($block['issues'])) {
            return self::ACTION_ERROR;
        }

        foreach (['addresses', 'contacts'] as $bucket) {
            foreach ($block[$bucket] as $entry) {
                if (self::hasError($entry['issues'])) {
                    return self::ACTION_ERROR;
                }
            }
        }

        return $block['existing'] !== null ? self::ACTION_SKIP : self::ACTION_CREATE;
    }

    private function summarize(array $blocks, array $orphans): array
    {
        $summary = [
            'organizations' => count($blocks),
            'create' => 0,
            'skip' => 0,
            'error' => 0,
            'addresses' => 0,
            'contacts' => 0,
            'warnings' => 0,
            'orphan_addresses' => count($orphans['addresses']),
            'orphan_contacts' => count($orphans['contacts']),
        ];

        foreach ($blocks as $block) {
            $summary[$block['action']]++;
            $summary['addresses'] += count($block['addresses']);
            $summary['contacts'] += count($block['contacts']);
            $summary['warnings'] += self::countWarnings($block['issues']);

            foreach (['addresses', 'contacts'] as $bucket) {
                foreach ($block[$bucket] as $entry) {
                    $summary['warnings'] += self::countWarnings($entry['issues']);
                }
            }
        }

        return $summary;
    }

    private static function hasError(array $issues): bool
    {
        foreach ($issues as $issue) {
            if ($issue['severity'] === ImportRowValidator::SEVERITY_ERROR) {
                return true;
            }
        }

        return false;
    }

    private static function countWarnings(array $issues): int
    {
        return count(array_filter(
            $issues,
            fn (array $issue) => $issue['severity'] === ImportRowValidator::SEVERITY_WARNING
        ));
    }

    private static function error(string $field, string $message): array
    {
        return ['severity' => ImportRowValidator::SEVERITY_ERROR, 'field' => $field, 'message' => $message];
    }

    private static function warning(string $field, string $message): array
    {
        return ['severity' => ImportRowValidator::SEVERITY_WARNING, 'field' => $field, 'message' => $message];
    }
}
