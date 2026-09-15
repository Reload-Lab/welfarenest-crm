<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indirizzo utile = via + città. Numero civico e CAP possono legittimamente
 * mancare: "Via Roma s.n.c.", località senza numerazione, caselle postali.
 * Province e region erano già state rese nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street_number')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street_number')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
        });
    }
};
