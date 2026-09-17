<?php

namespace App\Services\Import;

use Illuminate\Support\Str;

/**
 * Chiave di confronto fra denominazioni di organizzazioni.
 *
 * Minuscole, accenti rimossi, ogni carattere non alfanumerico eliminato —
 * spazi compresi. Così "Aon Spa", "AON S.p.A." e "aon  spa" danno la stessa
 * chiave, mentre "Fondo FASA" e "Fondo FAST" restano distinte.
 *
 * Deliberatamente NON rimuove le forme societarie: togliere "Srl" o "Spa"
 * farebbe collassare ragioni sociali diverse dello stesso gruppo. La
 * variante "nome commerciale contro ragione sociale" si copre confrontando
 * in croce i due campi, non allargando la chiave.
 */
class OrganizationNameKey
{
    public static function of(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = strtolower(Str::ascii($value));
        $value = preg_replace('/[^a-z0-9]/', '', $value);

        return $value === '' ? null : $value;
    }

    /** Tutte le chiavi di un'organizzazione: nome commerciale e ragione sociale. */
    public static function allOf(?string $name, ?string $legalName): array
    {
        return array_values(array_unique(array_filter([
            self::of($name),
            self::of($legalName),
        ])));
    }
}
