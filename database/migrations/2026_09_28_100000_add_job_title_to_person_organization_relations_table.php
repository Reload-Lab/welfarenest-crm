<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person_organization_relations', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('person_organization_relations', function (Blueprint $table) {
            $table->dropColumn('job_title');
        });
    }
};
