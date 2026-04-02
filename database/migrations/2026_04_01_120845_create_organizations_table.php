<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('name')->nullable();
            $table->string('legal_name')->nullable();

            $table->unsignedBigInteger('organization_type_id');

            $table->string('vat_number')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('sdi_code')->nullable();

            $table->boolean('is_split_payment')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('organization_type_id')
                ->references('id')
                ->on('organization_types')
                ->restrictOnDelete();

            $table->index('organization_type_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};