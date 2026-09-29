<?php

namespace App\Support;

use App\Models\ConsentType;
use App\Models\ConsentVersion;

/**
 * Risolve la frase da mostrare accanto a una casella di consenso.
 *
 * I testi stanno in config/consent_statements.php; qui c'è solo l'ordine di
 * ricerca, che deve essere identico ovunque (form pubblico dei consent request
 * e portale WN+) perché la stessa informativa non può apparire con due
 * formulazioni diverse a seconda della pagina.
 */
class ConsentStatement
{
    /**
     * @param  string  $code  codice del ConsentType (es. 'privacy_notice')
     * @param  string|null  $versionCode  codice della versione dell'informativa,
     *                                    quando il testo dipende dal documento
     */
    public static function for (
        string $code,
        ?string $versionCode = null,
        ?ConsentVersion $version = null,
        ?ConsentType $type = null
    ): string {
        $versionCode ??= $version?->version_code;

        if ($versionCode) {
            $specific = config("consent_statements.versions.{$versionCode}.{$code}");

            if (is_string($specific) && $specific !== '') {
                return $specific;
            }
        }

        $generic = config("consent_statements.{$code}");

        if (is_string($generic) && $generic !== '') {
            return $generic;
        }

        return $version?->title
            ?? $type?->name
            ?? $version?->consentType?->name
            ?? $code;
    }
}
