<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_versions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('consent_type_id');

            $table->string('version_code');
            $table->string('title');
            $table->text('content_text')->nullable();
            $table->string('content_file_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->dateTime('published_at')->nullable();

            $table->timestamps();

            $table->foreign('consent_type_id')
                ->references('id')
                ->on('consent_types')
                ->restrictOnDelete();

            $table->index('consent_type_id');
            $table->index('is_active');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_versions');
    }
};