<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Impostazioni privacy a livello di applicazione (per ora: solo riferimento al DPO).
 *
 * Non è legata a un singolo ConsentType: è un dato aziendale unico. Pensata come riga
 * singola (singleton) — usare PrivacySettings::current() per leggerla/crearla.
 *
 * Tutti i campi sono nullable e non popolati di default: Welfare Nest non ha, allo stato
 * attuale dei documenti forniti, un DPO nominato nei testi delle informative. Se in futuro
 * verrà nominato un DPO, questa è la tabella dove registrarne i contatti.
 */
class PrivacySettings extends Model
{
    protected $table = 'privacy_settings';

    protected $fillable = [
        'dpo_name',
        'dpo_email',
        'dpo_phone',
        'dpo_notes',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
