<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrganizationWorkbookReader
{
    public const SHEET_ORGANIZATIONS = 'Organizzazioni';
    public const SHEET_ADDRESSES = 'Indirizzi';
    public const SHEET_CONTACTS = 'Recapiti';

    private const HEADER_ROW = 1;

    /** Righe 2 e 3 sono didascalie del template, i dati iniziano dalla 4. */
    private const FIRST_DATA_ROW = 4;

    /** Tracciato atteso. L'ordine delle colonne nel file è irrilevante: conta il nome. */
    private const COLUMNS = [
        self::SHEET_ORGANIZATIONS => [
            'Codice_Organizzazione', 'name', 'legal_name', 'organization_type',
            'ruolo_cliente', 'ruolo_fornitore', 'ruolo_interno',
            'vat_number', 'tax_code', 'sdi_code', 'is_split_payment',
        ],
        self::SHEET_ADDRESSES => [
            'Organizzazione', 'Codice_Organizzazione', 'address_type',
            'street', 'street_number', 'postal_code', 'city', 'province',
            'country', 'is_primary',
        ],
        self::SHEET_CONTACTS => [
            'Organizzazione', 'Codice_Organizzazione', 'contact_type',
            'contact_usage', 'value', 'is_primary',
        ],
    ];

    /**
     * Colonne che da sole non fanno una riga: sono i flag che si trascinano
     * per sbaglio oltre la fine dei dati.
     */
    private const FLAG_COLUMNS = [
        self::SHEET_ORGANIZATIONS => ['ruolo_cliente', 'ruolo_fornitore', 'ruolo_interno', 'is_split_payment'],
        self::SHEET_ADDRESSES => ['is_primary'],
        self::SHEET_CONTACTS => ['is_primary'],
    ];

    /**
     * Fogli di vocabolario: portano le coppie code/name valide nel momento in
     * cui il file è stato generato. Sono il ponte che rende il file immune
     * alle rinomine successive delle anagrafiche.
     */
    private const VOCABULARY_SHEETS = [
        'Fonte_TipiOrganizzazione' => 'organization_types',
        'Fonte_RuoliOrganizzazione' => 'organization_roles',
        'Fonte_TipiIndirizzo' => 'address_types',
        'Fonte_TipiContatto' => 'contact_types',
        'Fonte_UtilizzoContatti' => 'contact_usages',
    ];

    /**
     * @return array{sheets: array<string, list<array>>, vocabulary: array<string, list<array>>, warnings: list<string>}
     *
     * @throws ImportStructureException
     */
    public function read(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new ImportStructureException("File non leggibile: {$path}");
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $sheets = [];
        $warnings = [];

        foreach (self::COLUMNS as $sheetName => $expected) {
            $sheet = $spreadsheet->getSheetByName($sheetName);

            if ($sheet === null) {
                throw new ImportStructureException(
                    "Foglio '{$sheetName}' assente. Il file non sembra il template di import."
                );
            }

            [$map, $sheetWarnings] = $this->mapColumns($sheet, $sheetName, $expected);
            [$rows, $ghostRows] = $this->extractRows($sheet, $sheetName, $map);

            if ($ghostRows !== []) {
                $sheetWarnings[] = sprintf(
                    "Foglio '%s': %d righe ignorate perché contenevano solo caselle di spunta (righe %s).",
                    $sheetName,
                    count($ghostRows),
                    self::compactRanges($ghostRows)
                );
            }

            $sheets[$sheetName] = $rows;
            $warnings = array_merge($warnings, $sheetWarnings);
        }

        [$vocabulary, $vocabularyWarnings] = $this->readVocabulary($spreadsheet);

        return [
            'sheets' => $sheets,
            'vocabulary' => $vocabulary,
            'warnings' => array_merge($warnings, $vocabularyWarnings),
        ];
    }

    /**
     * Legge i fogli Fonte_: intestazione alla riga 1, dati dalla 2.
     *
     * @return array{0: array<string, list<array{code: string, name: string}>>, 1: list<string>}
     */
    private function readVocabulary(Spreadsheet $spreadsheet): array
    {
        $vocabulary = [];
        $warnings = [];

        foreach (self::VOCABULARY_SHEETS as $sheetName => $table) {
            $sheet = $spreadsheet->getSheetByName($sheetName);

            if ($sheet === null) {
                $warnings[] = "Foglio '{$sheetName}' assente: i valori di questa anagrafica "
                    .'verranno confrontati direttamente per nome con l\'archivio.';

                continue;
            }

            $codeColumn = null;
            $nameColumn = null;
            $lastColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            for ($column = 1; $column <= $lastColumn; $column++) {
                $header = self::headerKey((string) $sheet->getCell([$column, self::HEADER_ROW])->getValue());

                if ($header === 'code') {
                    $codeColumn = $column;
                } elseif ($header === 'name') {
                    $nameColumn = $column;
                }
            }

            if ($codeColumn === null || $nameColumn === null) {
                $warnings[] = "Foglio '{$sheetName}': mancano le colonne code/name, vocabolario ignorato.";

                continue;
            }

            $entries = [];
            $lastRow = $sheet->getHighestDataRow();

            for ($row = self::HEADER_ROW + 1; $row <= $lastRow; $row++) {
                $code = $this->cellValue($sheet, $codeColumn, $row);
                $name = $this->cellValue($sheet, $nameColumn, $row);

                if ($code === null || $name === null) {
                    continue;
                }

                $entries[] = ['code' => $code, 'name' => $name];
            }

            if ($entries !== []) {
                $vocabulary[$table] = $entries;
            }
        }

        return [$vocabulary, $warnings];
    }

    /**
     * Associa ogni colonna attesa al suo indice reale nel foglio.
     *
     * @return array{0: array<string, int>, 1: list<string>}
     */
    private function mapColumns(Worksheet $sheet, string $sheetName, array $expected): array
    {
        $present = [];
        $lastColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($column = 1; $column <= $lastColumn; $column++) {
            $header = trim((string) $sheet->getCell([$column, self::HEADER_ROW])->getValue());

            if ($header !== '') {
                $present[self::headerKey($header)] = ['index' => $column, 'label' => $header];
            }
        }

        $map = [];
        $missing = [];

        foreach ($expected as $name) {
            $key = self::headerKey($name);

            if (isset($present[$key])) {
                $map[$name] = $present[$key]['index'];
                unset($present[$key]);
            } else {
                $missing[] = $name;
            }
        }

        if ($missing !== []) {
            throw new ImportStructureException(
                "Foglio '{$sheetName}': colonne mancanti - ".implode(', ', $missing)
            );
        }

        $warnings = [];

        foreach ($present as $extra) {
            $warnings[] = "Foglio '{$sheetName}': colonna '{$extra['label']}' non prevista dal tracciato, ignorata.";
        }

        return [$map, $warnings];
    }

    /**
     * @return array{0: list<array>, 1: list<int>}
     */
    private function extractRows(Worksheet $sheet, string $sheetName, array $map): array
    {
        $flags = array_flip(self::FLAG_COLUMNS[$sheetName] ?? []);
        $rows = [];
        $ghostRows = [];
        $lastRow = $sheet->getHighestDataRow();

        for ($row = self::FIRST_DATA_ROW; $row <= $lastRow; $row++) {
            $data = ['_row' => $row];
            $hasAnyValue = false;
            $hasRealValue = false;

            foreach ($map as $name => $column) {
                $value = $this->cellValue($sheet, $column, $row);
                $data[$name] = $value;

                if ($value !== null) {
                    $hasAnyValue = true;

                    if (! isset($flags[$name])) {
                        $hasRealValue = true;
                    }
                }
            }

            if ($hasRealValue) {
                $rows[] = $data;
            } elseif ($hasAnyValue) {
                $ghostRows[] = $row;
            }
        }

        return [$rows, $ghostRows];
    }

    private function cellValue(Worksheet $sheet, int $column, int $row): ?string
    {
        $cell = $sheet->getCell([$column, $row]);
        $value = $cell->getValue();

        if ($value instanceof RichText) {
            $value = $value->getPlainText();
        }

        // Formula non risolta (file salvato senza ricalcolo): ripiego sul valore in cache.
        if (is_string($value) && str_starts_with($value, '=')) {
            $value = $cell->getOldCalculatedValue();
        }

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** [72,73,74,76] => "72-74, 76" */
    private static function compactRanges(array $rows): string
    {
        sort($rows);
        $parts = [];
        $start = $previous = array_shift($rows);

        foreach ($rows as $row) {
            if ($row === $previous + 1) {
                $previous = $row;

                continue;
            }

            $parts[] = $start === $previous ? (string) $start : "{$start}-{$previous}";
            $start = $previous = $row;
        }

        $parts[] = $start === $previous ? (string) $start : "{$start}-{$previous}";

        return implode(', ', $parts);
    }

    private static function headerKey(string $header): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($header)));
    }
}
