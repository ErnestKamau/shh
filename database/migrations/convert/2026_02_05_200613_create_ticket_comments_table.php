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
        if (Schema::hasTable('ticket_comments')) {
            return;
        }
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ticket_id')->index('idx_ticket_comments_ticket_id_40115811');
            $table->uuid('user_id')->index('idx_ticket_comments_user_id_2e0b5727');
            $table->text('comment');
            $table->boolean('is_internal')->default(false)->index('idx_ticket_comments_is_internal_a86a1d0c');
            $table->longText('mentions')->nullable();
            $table->uuid('parent_comment_id')->nullable()->index('idx_ticket_comments_parent_comment_id_b20abc23');
            $table->softDeletes();
            $table->timestamps();
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
