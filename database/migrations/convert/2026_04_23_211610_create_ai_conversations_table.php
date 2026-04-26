<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('user_id');
            $table->string('title')->fulltext();
            $table->boolean('is_pinned')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'updated_at'], 'idx_ai_conversations_user_id_updated_at_69e8ebf5');
            $table->foreign(['user_id'], 'fk_ai_conversations_user_id_99a7c1e5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
