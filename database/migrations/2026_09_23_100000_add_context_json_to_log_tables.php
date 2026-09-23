<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aggiunge una colonna di contesto ai log.
 *
 * Serve a registrare l'origine dell'operazione (interfaccia CRM, comando da
 * console, importazione massiva, portale WN+, link pubblico di richiesta
 * consenso) senza sporcare old_values_json / new_values_json, che devono
 * contenere solo i valori dei campi dell'entita.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->json('context_json')->nullable()->after('new_values_json');
        });

        Schema::table('access_logs', function (Blueprint $table) {
            $table->json('context_json')->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn('context_json');
        });

        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn('context_json');
        });
    }
};
