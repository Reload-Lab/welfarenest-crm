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
        Schema::create('wn_plus_oidc_auth_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wn_plus_oidc_client_id')
                ->constrained('wn_plus_oidc_clients')
                ->cascadeOnDelete();

            $table->foreignId('wn_plus_account_id')
                ->constrained('wn_plus_accounts')
                ->cascadeOnDelete();

            $table->string('code')->unique();
            $table->string('redirect_uri');
            $table->string('scope')->nullable();
            $table->string('nonce')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wn_plus_oidc_auth_codes');
    }
};
