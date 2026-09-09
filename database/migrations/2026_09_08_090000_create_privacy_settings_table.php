<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabella segnaposto per il riferimento al DPO (Responsabile della Protezione dei Dati).
 *
 * Verificando i testi reali delle 14 informative fornite da Welfare Nest, nessuna riporta
 * il nominativo/contatto di un DPO: tutte rimandano solo ai contatti del Titolare
 * (privacy@welfarenest.it, PEC) e al Garante. L'art. 13/14 GDPR richiede i dati del DPO solo
 * "ove applicabile" (cioè se nominato), quindi l'assenza è legittima allo stato attuale.
 *
 * Questa tabella è un campo tecnico predisposto per il futuro: se/quando Welfare Nest
 * nominerà un DPO, i suoi dati andranno qui. Non viene seedata: resta vuota finché non
 * verrà valorizzata manualmente o da un'interfaccia di amministrazione.
 *
 * Il DPO è un dato aziendale unico (non varia per singola tipologia di consenso), per questo
 * si usa una tabella dedicata a riga singola invece di aggiungere colonne a consent_types.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('dpo_name')->nullable();
            $table->string('dpo_email')->nullable();
            $table->string('dpo_phone')->nullable();
            $table->text('dpo_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_settings');
    }
};
