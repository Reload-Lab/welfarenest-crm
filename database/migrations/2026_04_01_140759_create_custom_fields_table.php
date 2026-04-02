<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('entity_type');
            $table->string('field_type');

            $table->unsignedBigInteger('organization_type_id')->nullable();

            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);

            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('organization_type_id')
                ->references('id')
                ->on('organization_types')
                ->nullOnDelete();

            $table->index('entity_type');
            $table->index('field_type');
            $table->index('organization_type_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};