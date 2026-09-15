<?php

namespace App\Services\Import;

use App\Support\ItalianProvinces;

class FieldNormalizer
{
    /** Spazi Unicode che non sono lo spazio normale (NBSP, spazi tipografici, ideografico). */
    private const SPACE_LIKE = '/[\x{00A0}\x{1680}\x{2000}-\x{200A}\x{202F}\x{205F}\x{3000}]/u';

    /** Caratteri a larghezza zero: si eliminano, non si sostituiscono. */
    private const ZERO_WIDTH = '/[\x{200B}-\x{200D}\x{2060}\x{FEFF}]/u';

    /**
     * Ripulisce una cella prima di qualsiasi controllo: caratteri invisibili
     * eliminati, spazi anomali ricondotti a spazio normale, spazi multipli
     * collassati, estremi tagliati.
     *
     * Serve perché un NBSP invisibile in coda a una ragione sociale passerebbe
     * ogni validazione e romperebbe poi ogni confronto esatto.
     */
    private static function clean(?string $value): string
    {
        $value = (string) $value;
        $value = preg_replace(self::ZERO_WIDTH, '', $value);
        $value = preg_replace(self::SPACE_LIKE, ' ', $value);
        $value = preg_replace('/[\s]+/u', ' ', $value);

        return trim($value);
    }

    public static function text(?string $value): ?string
    {
        $value = self::clean($value);

        return $value === '' ? null : $value;
    }

    /** 1/0, sì/no, x, true/false. Vuoto = false. */
    public static function boolean(?string $value): NormalizedValue
    {
        $raw = strtolower(self::clean($value));

        if ($raw === '') {
            return NormalizedValue::ok(false);
        }

        if (in_array($raw, ['1', 'si', 'sì', 'x', 'true', 'vero', 'y', 's'], true)) {
            return NormalizedValue::ok(true);
        }

        if (in_array($raw, ['0', 'no', 'false', 'falso', 'n'], true)) {
            return NormalizedValue::ok(false);
        }

        return NormalizedValue::invalid("valore '{$raw}' non riconosciuto: usare 1 o 0");
    }

    /** Partita IVA italiana: 11 cifre. Il checksum errato è un avviso, non un errore. */
    public static function vatNumber(?string $value): NormalizedValue
    {
        $raw = strtoupper(str_replace(' ', '', self::clean($value)));

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        if (str_starts_with($raw, 'IT')) {
            $raw = substr($raw, 2);
        }

        $digits = preg_replace('/[^0-9]/', '', $raw);

        if ($digits !== $raw) {
            return NormalizedValue::invalid("'{$raw}' contiene caratteri non numerici");
        }

        if (strlen($digits) !== 11) {
            return NormalizedValue::invalid("'{$raw}' non è di 11 cifre");
        }

        return NormalizedValue::ok(
            $digits,
            self::vatChecksumIsValid($digits) ? null : "il codice di controllo di '{$digits}' non torna: verificare"
        );
    }

    /** Codice fiscale: 11 cifre (persone giuridiche) o 16 alfanumerici. */
    public static function taxCode(?string $value): NormalizedValue
    {
        $raw = strtoupper(str_replace(' ', '', self::clean($value)));

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        if (preg_match('/^[0-9]{11}$/', $raw)) {
            return NormalizedValue::ok(
                $raw,
                self::vatChecksumIsValid($raw) ? null : "il codice di controllo di '{$raw}' non torna: verificare"
            );
        }

        // Forma a 16 caratteri: verifichiamo l'impianto, non il carattere di controllo
        // (l'omocodia sostituisce cifre con lettere e lo renderebbe inaffidabile qui).
        if (preg_match('/^[A-Z0-9]{16}$/', $raw)) {
            return NormalizedValue::ok($raw);
        }

        return NormalizedValue::invalid("'{$raw}' non è né 11 cifre né 16 caratteri");
    }

    /** CAP: 5 cifre. Reintegra gli zeri iniziali persi da Excel. */
    public static function postalCode(?string $value): NormalizedValue
    {
        $raw = self::clean($value);

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        if (! preg_match('/^[0-9]{1,5}$/', $raw)) {
            return NormalizedValue::invalid("'{$raw}' non è un CAP valido");
        }

        return NormalizedValue::ok(str_pad($raw, 5, '0', STR_PAD_LEFT));
    }

    /** Sigla provincia: validata e ricondotta a quella corrente. */
    public static function province(?string $value): NormalizedValue
    {
        $raw = strtoupper(self::clean($value));

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        $canonical = ItalianProvinces::canonical($raw);

        if ($canonical === null) {
            return NormalizedValue::invalid("'{$raw}' non è una sigla di provincia valida");
        }

        return NormalizedValue::ok(
            $canonical,
            ItalianProvinces::isDeprecated($raw) ? "la sigla '{$raw}' è soppressa, ricondotta a '{$canonical}'" : null
        );
    }

    /** Telefono: separatori ammessi, lettere no. */
    public static function phone(?string $value): NormalizedValue
    {
        $raw = self::clean($value);

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        if (preg_match('/[a-zA-Z]/', $raw)) {
            return NormalizedValue::invalid("'{$raw}' contiene lettere");
        }

        if (! preg_match('#^\+?[0-9 ()./-]+$#', $raw)) {
            return NormalizedValue::invalid("'{$raw}' contiene caratteri non ammessi");
        }

        if (strlen(preg_replace('/[^0-9]/', '', $raw)) < 6) {
            return NormalizedValue::invalid("'{$raw}' è troppo corto per essere un numero di telefono");
        }

        return NormalizedValue::ok($raw);
    }

    public static function email(?string $value): NormalizedValue
    {
        $raw = strtolower(str_replace(' ', '', self::clean($value)));

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        if (! filter_var($raw, FILTER_VALIDATE_EMAIL)) {
            return NormalizedValue::invalid("'{$raw}' non è un indirizzo valido");
        }

        return NormalizedValue::ok($raw);
    }

    /** URL: se manca lo schema viene aggiunto https://, con avviso. */
    public static function url(?string $value): NormalizedValue
    {
        $raw = str_replace(' ', '', self::clean($value));

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        $warning = null;

        if (! preg_match('#^https?://#i', $raw)) {
            $raw = 'https://'.$raw;
            $warning = "schema mancante, normalizzato in '{$raw}'";
        }

        if (! filter_var($raw, FILTER_VALIDATE_URL)) {
            return NormalizedValue::invalid("'{$raw}' non è un indirizzo web valido");
        }

        return NormalizedValue::ok($raw, $warning);
    }

    /** Codice destinatario SDI: 6 caratteri (PA) o 7 (privati). */
    public static function sdiCode(?string $value): NormalizedValue
    {
        $raw = strtoupper(str_replace(' ', '', self::clean($value)));

        if ($raw === '') {
            return NormalizedValue::ok(null);
        }

        if (preg_match('/^[0-9]{11}$/', $raw)) {
            return NormalizedValue::invalid(
                "'{$raw}' ha 11 cifre: sembra una partita IVA inserita nella colonna sbagliata"
            );
        }

        if (! preg_match('/^[A-Z0-9]{6,7}$/', $raw)) {
            return NormalizedValue::invalid("'{$raw}' non è un codice destinatario valido");
        }

        return NormalizedValue::ok($raw);
    }

    /** Algoritmo di controllo della partita IVA italiana (variante di Luhn). */
    private static function vatChecksumIsValid(string $digits): bool
    {
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $digit = (int) $digits[$i];

            if ($i % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10 === (int) $digits[10];
    }
}
