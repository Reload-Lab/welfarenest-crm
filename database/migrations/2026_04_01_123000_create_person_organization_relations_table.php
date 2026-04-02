<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_organization_relations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('people')
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->constrained('organizations')
                ->cascadeOnDelete();

            $table->foreignId('qualification_id')
                ->nullable()
                ->constrained('qualifications')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indici
            $table->index('person_id');
            $table->index('organization_id');
            $table->index('qualification_id');
            $table->index('department_id');
            $table->index('is_primary');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_organization_relations');
    }
};