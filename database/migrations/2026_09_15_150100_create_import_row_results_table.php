<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traccia quale riga del file ha prodotto quale record, senza aggiungere
 * colonne alle tabelle di dominio. Permette di ricostruire cosa ha fatto
 * un'importazione e, volendo, di annullarla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_row_results', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('import_batch_id');

            $table->string('sheet');
            $table->unsignedInteger('row_number');

            // Stringa controllata, mai un FQCN Laravel:
            // organization | address | contact_point | organization_role_assignment
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');

            $table->timestamps();

            $table->foreign('import_batch_id')
                ->references('id')
                ->on('import_batches')
                ->cascadeOnDelete();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['import_batch_id', 'sheet']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_row_results');
    }
};
