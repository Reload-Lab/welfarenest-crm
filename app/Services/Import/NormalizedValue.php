<?php

namespace App\Services\Import;

/**
 * Esito della normalizzazione di un singolo campo: il valore ripulito,
 * più un eventuale errore (blocca la riga) o avviso (non la blocca).
 */
readonly class NormalizedValue
{
    public function __construct(
        public mixed $value = null,
        public ?string $error = null,
        public ?string $warning = null,
    ) {}

    public static function ok(mixed $value, ?string $warning = null): self
    {
        return new self($value, null, $warning);
    }

    public static function invalid(string $error): self
    {
        return new self(null, $error);
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }
}
