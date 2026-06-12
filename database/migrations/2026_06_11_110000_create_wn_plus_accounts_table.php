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
        Schema::create('wn_plus_accounts', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('person_id')
                ->nullable()
                ->constrained('people')
                ->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();

            $table->string('password')->nullable();

            $table->foreignId('wn_plus_role_id')
                ->constrained('wn_plus_roles')
                ->restrictOnDelete();

            $table->foreignId('wn_plus_level_id')
                ->constrained('wn_plus_levels')
                ->restrictOnDelete();

            $table->string('status')->default('invited');
            // invited | active | suspended | disabled

            $table->unsignedInteger('max_users')->nullable();

            $table->foreignId('invited_by_account_id')
                ->nullable()
                ->constrained('wn_plus_accounts')
                ->nullOnDelete();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();

            $table->index(['organization_id', 'wn_plus_role_id']);
            $table->index(['organization_id', 'wn_plus_level_id']);
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wn_plus_accounts');
    }
};
