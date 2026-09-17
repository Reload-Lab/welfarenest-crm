<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('filename');

            // Impronta del file: riconosce il ricarico dello stesso identico
            // file e avvisa l'operatore prima che ripeta l'operazione.
            $table->string('file_hash', 64);

            $table->string('status')->default('completed');

            $table->unsignedInteger('organizations_created')->default(0);
            $table->unsignedInteger('addresses_created')->default(0);
            $table->unsignedInteger('contacts_created')->default(0);
            $table->unsignedInteger('blocks_skipped')->default(0);
            $table->unsignedInteger('blocks_excluded')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('file_hash');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
