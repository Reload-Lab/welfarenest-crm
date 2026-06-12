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
        Schema::create('wn_plus_invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wn_plus_account_id')
                ->constrained('wn_plus_accounts')
                ->cascadeOnDelete();

            $table->string('token')->unique();

            $table->timestamp('expires_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            $table->index(['wn_plus_account_id', 'accepted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wn_plus_invitations');
    }
};
