<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wn_plus_accounts', function (Blueprint $table) {
            $table->string('account_type')
                ->default('user')
                ->after('status');

            $table->index(['organization_id', 'account_type']);
        });
    }

    public function down(): void
    {
        Schema::table('wn_plus_accounts', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'account_type']);
            $table->dropColumn('account_type');
        });
    }
};
