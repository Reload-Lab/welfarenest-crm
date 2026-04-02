<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('consent_type_id');
            $table->unsignedBigInteger('consent_version_id')->nullable();

            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');

            $table->string('status');
            $table->dateTime('granted_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->string('source')->nullable();

            $table->timestamps();

            $table->foreign('consent_type_id')
                ->references('id')
                ->on('consent_types')
                ->restrictOnDelete();

            $table->foreign('consent_version_id')
                ->references('id')
                ->on('consent_versions')
                ->nullOnDelete();

            $table->index(['owner_type', 'owner_id']);
            $table->index('consent_type_id');
            $table->index('consent_version_id');
            $table->index('status');
            $table->index('granted_at');
            $table->index('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};