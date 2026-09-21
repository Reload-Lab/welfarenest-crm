<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Il lead è una persona fisica non ancora in anagrafica: porta
            // con sé l'identità, mentre `name` resta il titolo/oggetto del
            // lead (es. "Richiesta informazioni da convegno").
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');

            // Azienda dichiarata dal contatto, testo libero: non è ancora
            // un'organizzazione. Diventa `organization_id` alla conversione.
            $table->string('company_name')->nullable()->after('last_name');

            // Chiusura: valorizzate all'ingresso in uno stato finale.
            $table->dateTime('closed_at')->nullable()->after('is_active');
            $table->string('lost_reason')->nullable()->after('closed_at');

            // Conversione: distingue un lead collegato a un'anagrafica
            // preesistente da un lead che l'anagrafica l'ha generata.
            $table->dateTime('converted_at')->nullable()->after('lost_reason');
            $table->unsignedBigInteger('converted_by_user_id')->nullable()->after('converted_at');

            $table->foreign('converted_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('last_name');
            $table->index('closed_at');
            $table->index('converted_at');
        });

        Schema::table('lead_statuses', function (Blueprint $table) {
            // Stati finali. Regola applicativa: al massimo uno dei due vero.
            // Servono per non dover cablare i `code` nel codice: gli stati
            // sono rinominabili dall'interfaccia di gestione.
            $table->boolean('is_won')->default(false)->after('description');
            $table->boolean('is_lost')->default(false)->after('is_won');

            $table->index(['is_won', 'is_lost']);
        });
    }

    public function down(): void
    {
        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->dropIndex(['is_won', 'is_lost']);

            $table->dropColumn(['is_won', 'is_lost']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_by_user_id']);

            $table->dropIndex(['last_name']);
            $table->dropIndex(['closed_at']);
            $table->dropIndex(['converted_at']);

            $table->dropColumn([
                'first_name',
                'last_name',
                'company_name',
                'closed_at',
                'lost_reason',
                'converted_at',
                'converted_by_user_id',
            ]);
        });
    }
};
