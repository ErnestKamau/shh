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
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ticket_id')->index('idx_ticket_comments_ticket_id_f0fe81f9');
            $table->uuid('user_id')->index('idx_ticket_comments_user_id_4bfb8ca2');
            $table->text('comment');
            $table->boolean('is_internal')->default(false)->index('idx_ticket_comments_is_internal_90f2e184');
            $table->longText('mentions')->nullable();
            $table->uuid('parent_comment_id')->nullable()->index('ticket_comments_parent_comment_id_foreign');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign(['parent_comment_id'], 'fk_ticket_comments_parent_comment_id_5c4b5d16')->references(['id'])->on('ticket_comments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ticket_id'], 'fk_ticket_comments_ticket_id_f754f0bc')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], 'fk_ticket_comments_user_id_ba807265')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
