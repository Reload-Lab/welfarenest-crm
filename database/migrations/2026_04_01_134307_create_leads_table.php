<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();

            $table->unsignedBigInteger('lead_status_id');
            $table->unsignedBigInteger('lead_source_id')->nullable();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->date('expected_close_date')->nullable();

            $table->unsignedBigInteger('assigned_user_id')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // FK
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->nullOnDelete();

            $table->foreign('person_id')
                ->references('id')
                ->on('people')
                ->nullOnDelete();

            $table->foreign('lead_status_id')
                ->references('id')
                ->on('lead_statuses')
                ->restrictOnDelete();

            $table->foreign('lead_source_id')
                ->references('id')
                ->on('lead_sources')
                ->nullOnDelete();

            $table->foreign('assigned_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Indici
            $table->index('organization_id');
            $table->index('person_id');
            $table->index('lead_status_id');
            $table->index('lead_source_id');
            $table->index('assigned_user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};