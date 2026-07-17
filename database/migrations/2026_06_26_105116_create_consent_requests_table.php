<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consent_requests', function (Blueprint $table) {
            $table->id();

            $table->string('token')->unique();

            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');

            $table->foreignId('contact_point_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('expires_at');
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->string('status')->default('pending');
            $table->string('source')->default('email_request');

            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_requests');
    }
};
