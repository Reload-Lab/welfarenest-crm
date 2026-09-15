<?php

namespace App\Support;

class ItalianProvinces
{
    /** Sigle soppresse => sigla corrente */
    private const ALIASES = [
        'OT' => 'SS', // Olbia-Tempio => Sassari
        'OG' => 'NU', // Ogliastra => Nuoro
        'VS' => 'SU', // Medio Campidano => Sud Sardegna
        'CI' => 'SU', // Carbonia-Iglesias => Sud Sardegna
        'FO' => 'FC', // Forli => Forli-Cesena
        'PS' => 'PU', // Pesaro => Pesaro e Urbino
    ];

    private const REGIONS = [
        'Abruzzo' => ['AQ', 'CH', 'PE', 'TE'],
        'Basilicata' => ['MT', 'PZ'],
        'Calabria' => ['CS', 'CZ', 'KR', 'RC', 'VV'],
        'Campania' => ['AV', 'BN', 'CE', 'NA', 'SA'],
        'Emilia-Romagna' => ['BO', 'FC', 'FE', 'MO', 'PC', 'PR', 'RA', 'RE', 'RN'],
        'Friuli-Venezia Giulia' => ['GO', 'PN', 'TS', 'UD'],
        'Lazio' => ['FR', 'LT', 'RI', 'RM', 'VT'],
        'Liguria' => ['GE', 'IM', 'SP', 'SV'],
        'Lombardia' => ['BG', 'BS', 'CO', 'CR', 'LC', 'LO', 'MB', 'MI', 'MN', 'PV', 'SO', 'VA'],
        'Marche' => ['AN', 'AP', 'FM', 'MC', 'PU'],
        'Molise' => ['CB', 'IS'],
        'Piemonte' => ['AL', 'AT', 'BI', 'CN', 'NO', 'TO', 'VB', 'VC'],
        'Puglia' => ['BA', 'BR', 'BT', 'FG', 'LE', 'TA'],
        'Sardegna' => ['CA', 'NU', 'OR', 'SS', 'SU'],
        'Sicilia' => ['AG', 'CL', 'CT', 'EN', 'ME', 'PA', 'RG', 'SR', 'TP'],
        'Toscana' => ['AR', 'FI', 'GR', 'LI', 'LU', 'MS', 'PI', 'PO', 'PT', 'SI'],
        'Trentino-Alto Adige' => ['BZ', 'TN'],
        'Umbria' => ['PG', 'TR'],
        "Valle d'Aosta" => ['AO'],
        'Veneto' => ['BL', 'PD', 'RO', 'TV', 'VE', 'VI', 'VR'],
    ];

    private static ?array $flat = null;

    /** Sigla normalizzata e ricondotta a quella corrente, o null se non valida. */
    public static function canonical(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        $code = self::ALIASES[$code] ?? $code;

        return isset(self::flat()[$code]) ? $code : null;
    }

    public static function isValid(?string $code): bool
    {
        return self::canonical($code) !== null;
    }

    /** Regione corrispondente alla sigla, o null se la sigla non e' valida. */
    public static function regionFor(?string $code): ?string
    {
        $code = self::canonical($code);

        return $code === null ? null : self::flat()[$code];
    }

    /** true se la sigla e' stata ricondotta da un codice soppresso. */
    public static function isDeprecated(?string $code): bool
    {
        return isset(self::ALIASES[strtoupper(trim((string) $code))]);
    }

    private static function flat(): array
    {
        if (self::$flat === null) {
            self::$flat = [];
            foreach (self::REGIONS as $region => $codes) {
                foreach ($codes as $c) {
                    self::$flat[$c] = $region;
                }
            }
        }

        return self::$flat;
    }
}
