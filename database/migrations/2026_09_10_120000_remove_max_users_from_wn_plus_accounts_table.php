<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Il cliente ha eliminato il limite di utenti invitabili per referente WN+
     * (vedi claude/analisi-inviti-wnplus.md): rimosso anche il campo, non solo
     * l'enforcement applicativo.
     */
    public function up(): void
    {
        Schema::table('wn_plus_accounts', function (Blueprint $table) {
            $table->dropColumn('max_users');
        });
    }

    public function down(): void
    {
        Schema::table('wn_plus_accounts', function (Blueprint $table) {
            $table->unsignedInteger('max_users')->nullable();
        });
    }
};
