<?php

namespace App\Models\Concerns;

use App\Observers\AuditObserver;

/**
 * Registra automaticamente su audit_logs ogni creazione, modifica e
 * cancellazione del model che usa questo trait.
 *
 * Per escludere altri campi dal registro, dichiarare nel model:
 *
 *     protected array $auditExclude = ['campo_da_non_registrare'];
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditObserver::class);
    }

    /**
     * Campi mai registrati nel log, nemmeno come valore vecchio.
     *
     * @return array<int, string>
     */
    public function auditExcludedAttributes(): array
    {
        return array_values(array_unique(array_merge(
            [
                'password',
                'remember_token',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'created_at',
                'updated_at',
            ],
            $this->getHidden(),
            property_exists($this, 'auditExclude') ? $this->auditExclude : [],
        )));
    }
}
