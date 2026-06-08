<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');

            $table->unsignedBigInteger('author_user_id');

            $table->text('content');
            $table->string('note_type')->nullable();
            $table->boolean('is_pinned')->default(false);

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('author_user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->index(['owner_type', 'owner_id']);
            $table->index('author_user_id');
            $table->index('note_type');
            $table->index('is_pinned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};