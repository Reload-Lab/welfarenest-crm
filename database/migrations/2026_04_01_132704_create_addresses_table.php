<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');

            $table->unsignedBigInteger('address_type_id');

            $table->string('label');
            $table->string('street');
            $table->string('street_number');
            $table->string('postal_code');
            $table->string('city');
            $table->string('province');
            $table->string('region');
            $table->string('country');

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->foreign('address_type_id')
                ->references('id')
                ->on('address_types')
                ->restrictOnDelete();

            $table->index(['owner_type', 'owner_id']);
            $table->index('address_type_id');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};