<?php

namespace App\Services\Import;

use App\Support\ItalianProvinces;

/**
 * Valida e normalizza una singola riga del file di import.
 *
 * Restituisce sempre ['data' => payload normalizzato, 'issues' => problemi].
 * Un problema di severità 'error' rende la riga non importabile; 'warning'
 * la lascia passare ma la segnala in anteprima.
 */
class ImportRowValidator
{
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_WARNING = 'warning';

    /** Tipo di ripiego per le organizzazioni importate senza classificazione. */
    public const UNCLASSIFIED_TYPE_CODE = 'da_classificare';

    /** Colonna del foglio => code del ruolo in anagrafica. */
    private const ROLE_COLUMNS = [
        'ruolo_cliente' => 'client',
        'ruolo_fornitore' => 'supplier',
        'ruolo_istituzione' => 'institution',
    ];

    public function __construct(private readonly LookupResolver $lookups) {}

    public function validateOrganization(array $row): array
    {
        $issues = [];
        $data = [];

        $data['import_code'] = FieldNormalizer::text($row['Codice_Organizzazione'] ?? null);

        if ($data['import_code'] === null) {
            $issues[] = self::error('Codice_Organizzazione', 'obbligatorio: serve a collegare indirizzi e recapiti');
        }

        $data['name'] = FieldNormalizer::text($row['name'] ?? null);
        $data['legal_name'] = FieldNormalizer::text($row['legal_name'] ?? null);

        if ($data['name'] === null && $data['legal_name'] === null) {
            $issues[] = self::error('name', 'almeno uno fra name e legal_name deve essere valorizzato');
        }

        // Tipo mancante: l'organizzazione entra comunque, classificata come
        // "Da classificare". Un tipo scritto ma non riconosciuto resta invece
        // un errore: l'incompletezza è importabile, la scorrettezza no.
        $data['organization_type_id'] = $this->lookupId(
            'organization_types', $row['organization_type'] ?? null, 'organization_type', false, $issues
        );

        if ($data['organization_type_id'] === null && FieldNormalizer::text($row['organization_type'] ?? null) === null) {
            $fallback = $this->lookups->find('organization_types', self::UNCLASSIFIED_TYPE_CODE);

            if ($fallback === null) {
                $issues[] = self::error('organization_type', sprintf(
                    "non indicato, e il tipo di ripiego '%s' non esiste in anagrafica",
                    self::UNCLASSIFIED_TYPE_CODE
                ));
            } else {
                $data['organization_type_id'] = $fallback['id'];
                $issues[] = self::warning('organization_type', sprintf(
                    "non indicato: assegnato '%s', da correggere nel CRM",
                    $fallback['name']
                ));
            }
        }

        $data['role_ids'] = [];

        foreach (self::ROLE_COLUMNS as $column => $roleCode) {
            $flag = FieldNormalizer::boolean($row[$column] ?? null);

            if (! $flag->isValid()) {
                $issues[] = self::error($column, $flag->error);

                continue;
            }

            if ($flag->value !== true) {
                continue;
            }

            $id = $this->lookups->resolve('organization_roles', $roleCode);

            if ($id === null) {
                $issues[] = self::error($column, "il ruolo '{$roleCode}' non esiste in anagrafica");

                continue;
            }

            $data['role_ids'][] = $id;
        }

        $vat = FieldNormalizer::vatNumber($row['vat_number'] ?? null);
        $this->collect('vat_number', $vat, $issues);
        $data['vat_number'] = $vat->value;

        $taxCode = FieldNormalizer::taxCode($row['tax_code'] ?? null);
        $this->collect('tax_code', $taxCode, $issues);
        $data['tax_code'] = $taxCode->value;

        $sdi = FieldNormalizer::sdiCode($row['sdi_code'] ?? null);
        $this->collect('sdi_code', $sdi, $issues);
        $data['sdi_code'] = $sdi->value;

        $split = FieldNormalizer::boolean($row['is_split_payment'] ?? null);
        $this->collect('is_split_payment', $split, $issues);
        $data['is_split_payment'] = $split->value ?? false;

        return ['data' => $data, 'issues' => $issues];
    }

    public function validateAddress(array $row): array
    {
        $issues = [];
        $data = [];

        $data['organization_ref'] = FieldNormalizer::text($row['Organizzazione'] ?? null);
        $data['organization_code'] = FieldNormalizer::text($row['Codice_Organizzazione'] ?? null);

        if ($data['organization_ref'] === null) {
            $issues[] = self::error('Organizzazione', 'obbligatoria: la riga non è collegabile a nessuna organizzazione');
        }

        $data['address_type_id'] = $this->lookupId(
            'address_types', $row['address_type'] ?? null, 'address_type', true, $issues
        );

        // Indirizzo utile = via + città. Numero civico, CAP, provincia e regione
        // possono legittimamente mancare (s.n.c., località senza numerazione).
        foreach ([
            'street' => ['via', true],
            'street_number' => ['numero civico', false],
            'city' => ['città', true],
        ] as $field => [$label, $required]) {
            $value = FieldNormalizer::text($row[$field] ?? null);

            if ($value === null) {
                $issues[] = $required
                    ? self::error($field, "obbligatorio ({$label})")
                    : self::warning($field, "{$label} non indicato");
            }

            $data[$field] = $value;
        }

        $postal = FieldNormalizer::postalCode($row['postal_code'] ?? null);
        $this->collect('postal_code', $postal, $issues);

        if ($postal->isValid() && $postal->value === null) {
            $issues[] = self::warning('postal_code', 'non indicato');
        }

        $data['postal_code'] = $postal->value;

        $province = FieldNormalizer::province($row['province'] ?? null);
        $this->collect('province', $province, $issues);
        $data['province'] = $province->value;

        if ($province->isValid() && $province->value === null) {
            $issues[] = self::warning('province', 'non indicata: la regione non può essere determinata');
        }

        $data['region'] = ItalianProvinces::regionFor($province->value);

        $data['country'] = FieldNormalizer::text($row['country'] ?? null) ?? 'Italia';

        $primary = FieldNormalizer::boolean($row['is_primary'] ?? null);
        $this->collect('is_primary', $primary, $issues);
        $data['is_primary'] = $primary->value ?? false;

        return ['data' => $data, 'issues' => $issues];
    }

    public function validateContact(array $row): array
    {
        $issues = [];
        $data = [];

        $data['organization_ref'] = FieldNormalizer::text($row['Organizzazione'] ?? null);
        $data['organization_code'] = FieldNormalizer::text($row['Codice_Organizzazione'] ?? null);

        if ($data['organization_ref'] === null) {
            $issues[] = self::error('Organizzazione', 'obbligatoria: la riga non è collegabile a nessuna organizzazione');
        }

        $typeValue = FieldNormalizer::text($row['contact_type'] ?? null);

        $data['contact_type_id'] = $this->lookupId('contact_types', $typeValue, 'contact_type', true, $issues);
        $data['contact_usage_id'] = $this->lookupId('contact_usages', $row['contact_usage'] ?? null, 'contact_usage', false, $issues);

        $category = $this->lookups->category('contact_types', $typeValue);
        $rawValue = FieldNormalizer::text($row['value'] ?? null);

        if ($rawValue === null) {
            $issues[] = self::error('value', 'obbligatorio');
            $data['value'] = null;
        } else {
            // Il valore si valida in base alla categoria del tipo di recapito:
            // un'email come email, un sito come URL, un telefono come telefono.
            $result = match ($category) {
                'email' => FieldNormalizer::email($rawValue),
                'phone' => FieldNormalizer::phone($rawValue),
                'web' => FieldNormalizer::url($rawValue),
                default => NormalizedValue::ok($rawValue),
            };

            $this->collect('value', $result, $issues);
            $data['value'] = $result->value;
        }

        $primary = FieldNormalizer::boolean($row['is_primary'] ?? null);
        $this->collect('is_primary', $primary, $issues);
        $data['is_primary'] = $primary->value ?? false;

        return ['data' => $data, 'issues' => $issues];
    }

    /** Risolve un valore di anagrafica, registrando errore o avviso. */
    private function lookupId(string $table, ?string $value, string $field, bool $required, array &$issues): ?int
    {
        $value = FieldNormalizer::text($value);

        if ($value === null) {
            if ($required) {
                $issues[] = self::error($field, 'obbligatorio');
            }

            return null;
        }

        $entry = $this->lookups->find($table, $value);

        if ($entry === null) {
            $claimedCode = $this->lookups->claimedCode($table, $value);

            $issues[] = self::error($field, $claimedCode !== null
                ? "'{$value}' corrisponde al codice '{$claimedCode}', che non esiste in anagrafica"
                : "valore '{$value}' non presente in anagrafica");

            return null;
        }

        if (! $entry['active']) {
            $issues[] = self::warning($field, "'{$value}' è disattivato in anagrafica ma verrà comunque usato");
        }

        return $entry['id'];
    }

    private function collect(string $field, NormalizedValue $value, array &$issues): void
    {
        if ($value->error !== null) {
            $issues[] = self::error($field, $value->error);
        }

        if ($value->warning !== null) {
            $issues[] = self::warning($field, $value->warning);
        }
    }

    private static function error(string $field, string $message): array
    {
        return ['severity' => self::SEVERITY_ERROR, 'field' => $field, 'message' => $message];
    }

    private static function warning(string $field, string $message): array
    {
        return ['severity' => self::SEVERITY_WARNING, 'field' => $field, 'message' => $message];
    }
}
