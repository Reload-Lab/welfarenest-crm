<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_points', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');

            $table->unsignedBigInteger('contact_type_id');
            $table->unsignedBigInteger('contact_usage_id')->nullable();

            $table->string('value');
            $table->string('label')->nullable();

            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('contact_type_id')
                ->references('id')
                ->on('contact_types')
                ->restrictOnDelete();

            $table->foreign('contact_usage_id')
                ->references('id')
                ->on('contact_usages')
                ->nullOnDelete();

            $table->index(['owner_type', 'owner_id']);
            $table->index('contact_type_id');
            $table->index('contact_usage_id');
            $table->index('is_primary');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_points');
    }
};