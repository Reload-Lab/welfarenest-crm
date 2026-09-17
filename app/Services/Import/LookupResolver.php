<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Risolve i valori testuali del file di import verso gli id delle tabelle
 * di classificazione.
 *
 * Se il file porta con sé i fogli Fonte_, la risoluzione passa dal code:
 * cella -> vocabolario del file -> code -> archivio -> id. Così una rinomina
 * successiva delle anagrafiche non rompe i file già compilati.
 */
class LookupResolver
{
    private array $cache = [];

    private array $vocabulary = [];

    /**
     * Carica il vocabolario portato dal file: nome normalizzato => code.
     *
     * @param  array<string, list<array{code: string, name: string}>>  $vocabulary
     */
    public function useFileVocabulary(array $vocabulary): void
    {
        foreach ($vocabulary as $table => $entries) {
            foreach ($entries as $entry) {
                $key = self::key($entry['name']);

                if ($key !== '') {
                    $this->vocabulary[$table][$key] = $entry['code'];
                }
            }
        }
    }

    /** Il code che il vocabolario del file associa a questo valore, se c'è. */
    public function claimedCode(string $table, ?string $value): ?string
    {
        return $this->vocabulary[$table][self::key($value)] ?? null;
    }

    /** Restituisce l'id, oppure null se il valore non è risolvibile. */
    public function resolve(string $table, ?string $value): ?int
    {
        return $this->find($table, $value)['id'] ?? null;
    }

    /** Voce completa (id, code, name, category, active) o null. */
    public function find(string $table, ?string $value): ?array
    {
        $key = self::key($value);

        if ($key === '') {
            return null;
        }

        $index = $this->index($table);
        $claimedCode = $this->vocabulary[$table][$key] ?? null;

        if ($claimedCode !== null) {
            $entry = $index[self::key($claimedCode)] ?? null;

            // Il file dichiara un code preciso: o quello, o niente. Nessun
            // ripiego sul nome, che potrebbe puntare a un'altra voce.
            return ($entry !== null && $entry['code'] === $claimedCode) ? $entry : null;
        }

        return $index[$key] ?? null;
    }

    /** Categoria della voce (solo per contact_types), o null. */
    public function category(string $table, ?string $value): ?string
    {
        return $this->find($table, $value)['category'] ?? null;
    }

    /** true se il valore risolve verso una voce disattivata. */
    public function isInactive(string $table, ?string $value): bool
    {
        $entry = $this->find($table, $value);

        return $entry !== null && ! $entry['active'];
    }

    /** Etichette attive, per i messaggi di errore e la generazione del template. */
    public function labels(string $table): array
    {
        $names = [];

        foreach ($this->index($table) as $entry) {
            if ($entry['active']) {
                $names[$entry['code']] = $entry['name'];
            }
        }

        return array_values(array_unique($names));
    }

    private function index(string $table): array
    {
        if (isset($this->cache[$table])) {
            return $this->cache[$table];
        }

        $index = [];

        foreach (DB::table($table)->get() as $row) {
            $entry = [
                'id' => (int) $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'category' => $row->category ?? null,
                'active' => (bool) $row->is_active,
            ];

            $nameKey = self::key($row->name);

            if (isset($index[$nameKey]) && $index[$nameKey]['code'] !== $row->code) {
                throw new RuntimeException(
                    "Ambiguità in {$table}: '{$index[$nameKey]['name']}' e '{$row->name}' "
                    ."si normalizzano allo stesso valore. Rinominare una delle due."
                );
            }

            $index[$nameKey] = $entry;

            // Il code è un ripiego: non sovrascrive mai un name.
            $codeKey = self::key($row->code);
            $index[$codeKey] ??= $entry;
        }

        return $this->cache[$table] = $index;
    }

    /**
     * Chiave di confronto: minuscole, accenti rimossi, tutto ciò che non è
     * alfanumerico diventa separatore.
     */
    private static function key(?string $value): string
    {
        $value = strtolower(Str::ascii(trim((string) $value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim($value);
    }
}
