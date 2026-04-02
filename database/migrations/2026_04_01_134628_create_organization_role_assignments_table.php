<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_role_assignments', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('organization_role_id');

            $table->timestamps();

            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();

            $table->foreign('organization_role_id')
                ->references('id')
                ->on('organization_roles')
                ->restrictOnDelete();

            $table->index('organization_id');
            $table->index('organization_role_id');

            // Evita duplicati

            $table->unique(
                    ['organization_id', 'organization_role_id'],
                    'org_role_unique'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_role_assignments');
    }
};