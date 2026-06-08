<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consent_types', function (Blueprint $table) {
            $table->string('category')->default('consent')->after('name');
            $table->text('description')->nullable()->change();
        });

        Schema::table('consent_versions', function (Blueprint $table) {
            $table->unique(['consent_type_id', 'version_code']);
        });

        Schema::table('consents', function (Blueprint $table) {
            $table->dateTime('requested_at')->nullable()->after('status');
            $table->dateTime('denied_at')->nullable()->after('revoked_at');
            $table->foreignId('created_by_user_id')->nullable()->after('source')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('created_by_user_id');
            $table->string('evidence_file_path')->nullable()->after('notes');

            $table->index('requested_at');
            $table->index('denied_at');
        });
    }

    public function down(): void
    {
        Schema::table('consents', function (Blueprint $table) {
            $table->dropIndex(['requested_at']);
            $table->dropIndex(['denied_at']);
            $table->dropForeign(['created_by_user_id']);

            $table->dropColumn([
                'requested_at',
                'denied_at',
                'created_by_user_id',
                'notes',
                'evidence_file_path',
            ]);
        });

        Schema::table('consent_versions', function (Blueprint $table) {
            $table->dropUnique(['consent_type_id', 'version_code']);
        });

        Schema::table('consent_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};