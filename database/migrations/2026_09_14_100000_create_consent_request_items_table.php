<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Definisce QUALI consensi vengono proposti in una singola ConsentRequest
 * (il link inviato via email a un contatto). Prima di questa tabella,
 * ConsentRequest non sapeva a quali consent_type/consent_version si
 * riferisse: la pagina pubblica era un segnaposto senza alcun form.
 *
 * Un item per ogni consenso mostrato nel form pubblico: is_required=true
 * per la presa visione dell'informativa (blocca l'invio se non spuntata),
 * false per i consensi facoltativi (in quel caso l'assenza della spunta
 * viene registrata esplicitamente come "denied", non lasciata in sospeso).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_request_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('consent_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consent_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('consent_version_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['consent_request_id', 'consent_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_request_items');
    }
};
